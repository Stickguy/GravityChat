# GravityChat
A Gravity Forms Add On that creates Conversational Forms

Base code for the conversational form is from this library:
https://github.com/space10-community/conversational-form


__Install:__ Install Add-on into the WordPress Plugin Folder and activate.
Requires the Gravity Forms WordPress plugin

__Usage:__
Use the following shortcode with the ID of the Gravity Form you wish to display as a conversational form. The main motivation for using the standalone shortcode is to redraw the gravity form and it's fields in the standard field format expected by the conversational forms library.

The form will automatically submit when the last field is filled out.

To send the user to a custom URL use a radio field with one button as the last field in your form. Enter the desired URL as the Value of the button.

 Shortcode: [gravitychat id="1"]


__To Do:__

⋅⋅[ ] Add settings in plugin to customize chat icons
⋅⋅[ ] Clean up form settings area 
⋅⋅[ ] Add support for "chatbot style" modal popup to the shortcode
⋅⋅[ ] Add support for remaining Gravity Form Field Types
⋅⋅[ ] Add sample Gravity Forms for import to repository
