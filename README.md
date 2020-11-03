[![](https://img.shields.io/packagist/v/inspiredminds/contao-event-registration.svg)](https://packagist.org/packages/inspiredminds/contao-event-registration)
[![](https://img.shields.io/packagist/dt/inspiredminds/contao-event-registration.svg)](https://packagist.org/packages/inspiredminds/contao-event-registration)

Contao Event Registration
=========================

Contao extension to allow registration for events.


## Usage

After installation you will have the possibility to enable registration for individual events in the event's settings:

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/master/event-settings.png" width="734" alt="Event settings">

- **Registration form**: Select a form from the form generator which will be used for the registration. All the form's data will be saved for each registration. The form is processed as usual, i. e. notifications emails are also sent.
- **Minimum amount of participants**: You can define an optional minimum amount of participants, which you can then display in the front end (together with the currently registered participants).
- **Maximum amount of participants**: You can define an optional maximum amount of participants. If this number is reached, registration will not be possible anymore.
- **End of registraton**: You can define an optional date after which registration will not be possible anymore.
- **End of cancellation**: You cand efine an optional date after which cancellation will not be possible anymore.
- **Require confirmation**: When enabled, only confirmed registrations count towards the total number of registrations.


### Modules

The extension provides three new event modules:

- Event registration form
- Event registration confirmation
- Event registration cancellation

All of these modules are optional. The _Event registration form_ will need to be inserted on the same page as the event reader module and it will display the event registration form, if the event has registration enabled. Alternatively this form is also available as [template variables](#template-variables) within event templates.

The confirmation and cancellation forms can be inserted on other pages. In that case you also need to specify those pages in the settings of the calendar. Otherwise it is assumed that those modules are also present on the event reader page. The modules allow you to define a _node_ for detailed content. This content will be displayed, when an event registration has been successfully confirmed or cancelled. You are also able to select a notification that will be sent after succesful cancellation or confirmation. If you do not need either confirmation or cancellation functionality, these modules do not need to be created.


### Template Variables

The following template variables are available in event templates as well as the template for the event registration form:

- `$this->canRegister`: Whether registration is possible for this event.
- `$this->registrationForm`: Contains the registration form.
- `$this->isFull`: Whether the maximum amount of registrations have been reached for this event.
- `$this->registrationCount`: The current registration count for this event.
- `$this->reg_min`: The minimum amount of registrations for this event.
- `$this->reg_max`: The maximum amount of registrations for this event.
- `$this->reg_regEnd`: Timestamp after which registration is not possible anymore.
- `$this->reg_cancelEnd`: Timestamp after which cancellation is not possible anymore.


### Simple Tokens

Within notifications, _as well as the node content of the confirmation and cancellation modules_, the following simple tokens are available:

- `##event_*##`: All variables of the event.
- `##reg_*##`: All variables of the registration.
- `##reg_count##`: The respective registration count.
- `##reg_confirm_url##`: The URL with which the registration can be confirmed.
- `##reg_cancel_url##`: The URL with which the registration can be cancelled.


## Multiple Languages

This extensions supports [`terminal42/contao-changelanguage`](https://github.com/terminal42/contao-changelanguage). If an event has a main event defined, then the aforementioned option will only be available in the main event. Any registrations will always be associated and counted towards the main event, thus the total number of registrations will be the same across all events associated with the main event.

This also applies to the template variables mentioned above. They will always reference the main event, if available.


## Displaying and Exporting Registrations

For each event there is an additional icon in the event list in the back end.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/master/event-list.png" width="704" alt="Event list">

This allows you to list the registrations for each event.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/master/event-registrations.png" width="736" alt="Event registrations">

Within the list, you can view the details of the event registration using the info icon. You can also edit some properties of the event, like the amount of people for this registration and whether it has been confirmed or cancelled.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/master/event-registration.png" width="734" alt="Event registration">

The overview also allows you to export the registrations as a CSV. The export allows you to configure the delimiter (either `,` or `;`) and it allows you to export a Microsoft Excel compatible CSV.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/master/export.png" width="736" alt="Event registration export">


## User Defined Amount

By default each registration assumes the amount of 1 person for said registration. However it is also possible to allow the visitor who registers for an event to define the number of people for that registration. In order to do this insert a new text form field into the form (preferably with a numeric validation) with the field name `amount`. This will then override the default amount and the total amount of registrations will increase by this amount as well.
