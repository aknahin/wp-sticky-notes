function advance_notes_enqueue_scripts() {
    wp_enqueue_script('jquery-ui-draggable');

    $css = '
    #advance-notes-add-button {
        position: fixed;
        bottom: 10px;
        right: 10px;
        background: #0073aa;
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 1000;
    }

    #advance-notes-container {
        position: relative;
        z-index: 999;
    }

    .advance-note {
        position: fixed;
        width: 200px;
        background: yellow;
        padding: 10px;
        border: 1px solid #000;
        z-index: 1000;
    }

    .advance-note .note-content,
    .advance-note .note-text {
        width: 100%;
        height: 100px;
        margin-bottom: 10px;
    }

    .advance-note button {
        padding: 2px 5px;
        margin-right: 5px;
    }

    .advance-note .close-note {
        border: none;
        background: none;
        font-weight: bold;
        color: #000;
    }
    ';
    wp_add_inline_style('wp-block-library', $css);

    $js = '
    jQuery(document).ready(function($) {
        function loadNotes() {
            $.post(advanceNotesAjax.ajaxurl, {
                action: "advance_notes_load_notes",
                page_url: advanceNotesAjax.page_url
            }, function(response) {
                if (response.success) {
                    response.data.forEach(note => {
                        createNoteElement(note);
                    });
                }
            });
        }

        function createNoteElement(note) {
            var noteElement = $("<div class=\"advance-note\" data-id=\"" + note.id + "\"></div>");
            var noteContent = $("<div class=\"note-content\"></div>").text(note.note);
            var editButton = $("<button class=\"edit-note\">Edit</button>");
            var deleteButton = $("<button class=\"delete-note\">Delete</button>");
            var closeButton = $("<button class=\"close-note\">X</button>");
            var saveButton = $("<button class=\"save-note\" style=\"display: none;\">Save</button>");
            var textBox = $("<textarea class=\"note-text\" style=\"display: none;\"></textarea>").val(note.note);

            noteElement.append(noteContent, textBox, editButton, saveButton, deleteButton, closeButton);
            noteElement.css({top: note.top + "px", left: note.leftx + "px"});
            $("#advance-notes-container").append(noteElement);

            noteElement.draggable({
                stop: function() {
                    var offset = $(this).offset();
                    $(this).data("position", offset);
                }
            });

            editButton.click(function() {
                noteContent.hide();
                textBox.show();
                editButton.hide();
                saveButton.show();
            });

            saveButton.click(function() {
                var newNote = textBox.val();
                var position = noteElement.data("position") || {top: note.top, left: note.leftx};

                $.post(advanceNotesAjax.ajaxurl, {
                    action: "advance_notes_update_note",
                    id: note.id,
                    note: newNote,
                    top: position.top,
                    leftx: position.left,
                    page_url: advanceNotesAjax.page_url
                }, function(response) {
                    if (response.success) {
                        noteContent.text(newNote);
                        textBox.hide();
                        noteContent.show();
                        saveButton.hide();
                        editButton.show();
                    }
                });
            });

            deleteButton.click(function() {
                noteElement.remove();
                $.post(advanceNotesAjax.ajaxurl, {
                    action: "advance_notes_delete_note",
                    id: note.id
                });
            });

            closeButton.click(function() {
                noteElement.hide();
            });
        }

        $("#advance-notes-add-button").click(function() {
            var note = {note: "", top: 100, leftx: 100};

            $.post(advanceNotesAjax.ajaxurl, {
                action: "advance_notes_add_note",
                note: note.note,
                top: note.top,
                leftx: note.leftx,
                page_url: advanceNotesAjax.page_url
            }, function(response) {
                if (response.success) {
                    note.id = response.data.id;
                    createNoteElement(note);
                }
            });
        });

        loadNotes();
    });
    ';
    wp_add_inline_script('jquery', $js);

    wp_localize_script('jquery', 'advanceNotesAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'userid' => get_current_user_id(),
        'page_url' => get_permalink()
    ));
}
add_action('wp_enqueue_scripts', 'advance_notes_enqueue_scripts');

function advance_notes_add_buttons() {
    ?>
    <div id="advance-notes-container"></div>
    <button id="advance-notes-add-button">+</button>
    <?php
}
add_action('wp_footer', 'advance_notes_add_buttons');

function advance_notes_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advance_notes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        userid bigint(20) NOT NULL,
        page_url text NOT NULL,
        note text NOT NULL,
        top int(11) NOT NULL,
        leftx int(11) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'advance_notes_create_table');

function advance_notes_add_note() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advance_notes';
    if ( current_user_can( 'administrator' ) ) {
	  $userid = $_COOKIE['cmw_msg_user_id'];
	  if ( $userid ==0 ){
		  $userid = get_current_user_id();
	  }	
	} else {
		$userid = get_current_user_id();
	}
    $page_url = sanitize_text_field($_POST['page_url']);
    $note = sanitize_text_field($_POST['note']);
    $top = intval($_POST['top']);
    $leftx = intval($_POST['leftx']);

    $wpdb->insert($table_name, array(
        'userid' => $userid,
        'page_url' => $page_url,
        'note' => $note,
        'top' => $top,
        'leftx' => $leftx
    ));

    $id = $wpdb->insert_id;

    wp_send_json_success(array('id' => $id));
}
add_action('wp_ajax_advance_notes_add_note', 'advance_notes_add_note');

function advance_notes_update_note() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advance_notes';
    $id = intval($_POST['id']);
    $note = sanitize_text_field($_POST['note']);
    $top = intval($_POST['top']);
    $leftx = intval($_POST['leftx']);

    $wpdb->update($table_name, array(
        'note' => $note,
        'top' => $top,
        'leftx' => $leftx
    ), array('id' => $id));

    wp_send_json_success();
}
add_action('wp_ajax_advance_notes_update_note', 'advance_notes_update_note');

function advance_notes_delete_note() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advance_notes';
    $id = intval($_POST['id']);

    $wpdb->delete($table_name, array('id' => $id));

    wp_send_json_success();
}
add_action('wp_ajax_advance_notes_delete_note', 'advance_notes_delete_note');

function advance_notes_load_notes() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advance_notes';
    if ( current_user_can( 'administrator' ) ) {
	  $userid = $_COOKIE['cmw_msg_user_id'];
	  if ( $userid ==0 ){
		  $userid = get_current_user_id();
	  }	
	} else {
		$userid = get_current_user_id();
	}
    $page_url = sanitize_text_field($_POST['page_url']);

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE userid = %d AND page_url = %s",
        $userid, $page_url
    ));

    wp_send_json_success($results);
}
add_action('wp_ajax_advance_notes_load_notes', 'advance_notes_load_notes');
