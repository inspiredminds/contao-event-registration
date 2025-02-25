[![](https://img.shields.io/packagist/v/inspiredminds/contao-event-registration.svg)](https://packagist.org/packages/inspiredminds/contao-event-registration)
[![](https://img.shields.io/packagist/dt/inspiredminds/contao-event-registration.svg)](https://packagist.org/packages/inspiredminds/contao-event-registration)

Contao Event Registration
=========================

Contao extension to allow registration for events.


## Usage

After installation you will have the possibility to enable registration for individual events in the event's settings:

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/main/event-settings.png" width="734" alt="Event settings">

- **Registration form**: Select a form from the form generator which will be used for the registration. All the form's data will be saved for each registration. The form is processed as usual, i. e. notifications emails are also sent.
- **Minimum amount of participants**: You can define an optional minimum amount of participants, which you can then display in the front end (together with the currently registered participants).
- **Maximum amount of participants**: You can define an optional maximum amount of participants. If this number is reached, registration will not be possible anymore.
- **End of registration**: You can define an optional date after which registration will not be possible anymore.
- **End of cancellation**: You can define an optional date after which cancellation will not be possible anymore.
- **Require confirmation**: When enabled, only confirmed registrations count towards the total number of registrations.
- **Enable waiting list**: Keeps the registration open after the maximum amount of participants is reached. All registrations will be put on a waiting list and will automatically be advanced, if prior registrations are cancelled.
- **Advancement from waiting list notification**: This notification will be sent when a participant is advanced from the waiting list.


### Modules

The extension provides three new event modules:

- Event registration form
- Event registration confirmation
- Event registration cancellation
- Event registration list

All of these modules are optional. The _Event registration form_ will need to be inserted on the same page as the event reader module and it will display the event registration form, if the event has registration enabled. Alternatively this form is also available as [template variables](#template-variables) within event templates.

The confirmation and cancellation forms can be inserted on other pages. In that case you also need to specify those pages in the settings of the calendar. Otherwise it is assumed that those modules are also present on the event reader page. The modules allow you to define a _node_ for detailed content. This content will be displayed, when an event registration has been successfully confirmed or cancelled. You are also able to select a notification that will be sent after succesful cancellation or confirmation. If you do not need either confirmation or cancellation functionality, these modules do not need to be created.

The _Event registration list_ module can be used to display a list of registrations for the current event on the event reader page. The list will show only confirmed registrations if the registration confirmation is enabled and it will also not show any cancelled registrations. By default the `mod_event_registration_list` template will use an automatically generated label for each entry according to the [`list.label`](https://docs.contao.org/dev/reference/dca/list/#labels) configuration of the `tl_event_registration` DCA. The default configuration uses the fields `firstname` and `lastname` - however, if your own form does not have these values, you will need to create a custom `mod_even_registration_list` template and output the correct field(s) accordingly. All of the submitted values of the registration form for each registration are available under the `form_data` key. For example:

```php
<?php $this->extend('block_unsearchable'); ?>

<?php $this->block('content'); ?>
  <ul>
    <?php foreach ($this->registrations as $registration): ?>
      <li><?= $registration->form_data->my_form_field ?></li>
    <?php endforeach; ?>
  </ul>
<?php $this->endblock(); ?>
```


### Calendar Settings

As mentioned above there will also be additional settings in your calendars. Per calendar you can configure the redirect page for confirmations as well as the redirect page for cancellations. On these respective pages you then add either the _Event registration confirmation_ module or _Event registration cancellation_ module, if needed.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/main/calendar-settings.png" width="778" alt="Calendar settings">


### Template Variables

The following template variables are available in event templates as well as the template for the event registration form:

- `$this->canRegister`: Whether registration is possible for this event.
- `$this->registrationForm`: Contains the registration form.
- `$this->isFull`: Whether the maximum amount of registrations have been reached for this event.
- `$this->isWaitingList`: Whether the maximum amount of registrations have been reached for this event and waiting list is enabled.
- `$this->registrationCount`: The current registration count for this event.
- `$this->reg_min`: The minimum amount of registrations for this event.
- `$this->reg_max`: The maximum amount of registrations for this event.
- `$this->reg_regEnd`: Timestamp after which registration is not possible anymore.
- `$this->reg_cancelEnd`: Timestamp after which cancellation is not possible anymore.


### Simple Tokens

Within notifications, _as well as the node content of the confirmation and cancellation modules_, the following simple tokens are available:

- `##event_*##`: All variables of the event.
- `##reg_*##`: All variables of the registration (this includes all form data).
- `##reg_amount##`: The amount of people for one registration.
- `##reg_count##`: The respective registration count.
- `##reg_confirm_url##`: The URL with which the registration can be confirmed.
- `##reg_cancel_url##`: The URL with which the registration can be cancelled.


## Multiple Languages

This extensions supports [`terminal42/contao-changelanguage`](https://github.com/terminal42/contao-changelanguage). If an event has a main event defined, then the aforementioned option will only be available in the main event. Any registrations will always be associated and counted towards the main event, thus the total number of registrations will be the same across all events associated with the main event.

This also applies to the template variables mentioned above. They will always reference the main event, if available.


## Displaying and Exporting Registrations

For each event there is an additional icon in the event list in the back end.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/main/event-list.png" width="704" alt="Event list">

This allows you to list the registrations for each event.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/main/event-registrations.png" width="736" alt="Event registrations">

Within the list, you can view the details of the event registration using the info icon. You can also edit some properties of the event, like the amount of people for this registration and whether it has been confirmed or cancelled.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/main/event-registration.png" width="734" alt="Event registration">

The overview also allows you to export the registrations as a CSV. The export allows you to configure the delimiter (either `,` or `;`) and it allows you to export a Microsoft Excel compatible CSV.

<img src="https://raw.githubusercontent.com/inspiredminds/contao-event-registration/main/export.png" width="736" alt="Event registration export">


### Backend Configuration

The event registration list in the back end (as well as in the front end module) uses the fields `firstname` and `lastname` by default. However, if your event registration form does not have these fields, you might want to adjust the DCA configuration so that it will show a label suitable to your needs in the back end. For example if your form uses the fields `vorname`, `nachname` and `email` you could configure the back end labels as follows:

```php
// contao/dca/tl_event_registration.php
$GLOBALS['TL_DCA']['tl_event_registration']['list']['label']['fields'] = ['vorname', 'nachname', 'email'];
$GLOBALS['TL_DCA']['tl_event_registration']['list']['label']['format'] = '%s %s (%s)';
```

Be advised that this also affects the default labels of the _Event registration list_ front end module.


## User Defined Amount

By default each registration assumes the amount of 1 person for said registration. However it is also possible to allow the visitor who registers for an event to define the number of people for that registration. In order to do this insert a new text form field into the form (preferably with a numeric validation) with the field name `amount`. This will then override the default amount and the total amount of registrations will increase by this amount as well.


## Multiple Registrations

Starting with version `2.2.0` you can also allow visitors to register for multiple events at once. For this there now
two new features:

* An _Event registration calendar_ front end module which renders a normal calendar (just like the regular calendar)
  module with a checkbox for each event in the calendar.
* A _Selected event_ form field, which will display and later process the selection in a form of the form generator.

Overall the following needs to be done to use this feature:

1. Create a new notifiction which will be used for the registration of multiple events (the tokens are the same).
2. Create a new form which will be used for the registration of multiple events.
3. In this form insert a _Selected event_ form field.
4. Also select the appropriate notification.
5. Create a new page where you insert the new registration form (alternatively you could also insert it on the same page
   as the calendar).
6. Create a new _Event registration calendar_ module and select the previously created page as the redirect page (or
   none).
7. Insert this module into a new page.

If you visit that page in the front end, you will see checkboxes for each event for which the registration is enabled.
You can also switch between months - the previous selection will be stored. Once you click on "Continue" you will be
redirected to the registration form, where you will see a list of events that were selected for this registration.

After submitting the registration form, the form's notification will be sent as normal. If you had a
`##reg_confirm_url##` token in your notification, the confirm URL will automatically confirm the registrations for _all_
the selected events.

### Member Registration List

In addition to the aforementioned features, there is also a new _Event registrations_ front end module in the _User_
section. This module will list all event registrations of the currently logged in front end user. It will also show
links to _Confirm_ (if applicable) or _Cancel_ the registration.
