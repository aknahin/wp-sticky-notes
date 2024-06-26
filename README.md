# WP Sticky Notes

WP Sticky Notes is a WordPress feature that allows users to add sticky notes to specific pages. These notes are user-specific and page-specific, meaning that each user can have their own set of notes on each page. The notes can be moved, edited, saved, deleted, or temporarily hidden.

## Installation

1. **Copy Code**: Copy the contents of `advance-notes.php` and paste it into your theme's `functions.php` file.

2. **User Specific Notes**: To ensure that notes are user-specific, set the user ID in a cookie as `$_COOKIE['cmw_msg_user_id']`. This will allow only the specified user to see their notes.

## Features

- **User-Specific Notes**: Each note is tied to a specific user, ensuring privacy and customization.
- **Page-Specific Notes**: Notes are also tied to specific pages, allowing users to have different notes on different pages.
- **Draggable Notes**: Notes can be dragged to any position on the screen. Simply click and drag to move them.
- **Edit and Save**: Click the "Edit" button to modify a note. After making changes, click "Save" to update the note.
- **Delete**: The "Delete" button will permanently remove a note.
- **Temporary Hide**: The "Close" button will hide the note temporarily. It will reappear when the page is reloaded.

## Usage

1. **Adding a Note**: Click the "+" button at the bottom right corner of the screen to add a new note. The note will be saved with the current user's ID and the current page's URL.
2. **Editing a Note**: Click the "Edit" button on a note to modify its content. After editing, click "Save" to update the note in the database.
3. **Deleting a Note**: Click the "Delete" button to permanently remove a note from the database.
4. **Hiding a Note**: Click the "Close" button to hide the note temporarily. It will be visible again upon page reload.

## Technical Details

- **Database Table**: The notes are stored in the `wp_advance_notes` table with the following fields:
  - `id`: Unique identifier for each note.
  - `userid`: ID of the user who created the note.
  - `page_url`: URL of the page where the note was created.
  - `note`: Content of the note.
  - `top`: Top position of the note (in pixels).
  - `leftx`: Left position of the note (in pixels).

## Contributing

Contributions are welcome! If you find any bugs or have suggestions for improvements, please submit an issue or create a pull request.

## License

This project is licensed under the MIT License.
