## To-do

- [ ] Bilder hinzufügen
- [ ] Textformatierungen überprüfen

# Contao Event Registrierung

Diese Contao Erweiterung ermöglicht die Registrerung (Buchung) von Events.

## Anwendung

Nach der Installation können (einzelne) Events zur Registrierung (Buchung) freigegeben werden. Die Aktivierung wird in den Einstellungen des Events vorgenommen.

![Registrierung erlauben](images/registrierung-erlauben.png)

<ul>
  <li>Registrierungsformular: Wählen Sie aus dem Formulargenerator ein Formular aus, welches für die Registrierung verwendet werden soll. Alle Daten des Formulars werden bei jeder Registrierung gespeichert. Das Formular wird wie gewohnt verarbeitet, d.h. es werden auch Benachrichtigungsmails versendet.</li>
  <li>Mindestteilnehmerzahl: Du kannst optional eine Mindestteilnehmerzahl festlegen, die Du dann (zusammen mit den aktuell angemeldeten Teilnehmern) im Frontend anzeigen kannst.</li>
  <li>Maximale Teilnehmerzahl: Sie können optional eine maximale Teilnehmerzahl festlegen. Wird diese Zahl erreicht, ist eine Anmeldung nicht mehr möglich.</li>
  <li>Ende der Registrierung: Sie können optional ein Datum festlegen, nach dem eine Registrierung nicht mehr möglich ist.</li>
  <li>Ende der Stornierung: Sie können optional ein Datum festlegen, nach dem eine Stornierung nicht mehr möglich ist.</li>
  <li>Bestätigung erforderlich: Wenn aktiviert, werden nur bestätigte Registrierungen zur Gesamtzahl der Registrierungen gezählt.</li>
  <li>Warteliste aktivieren: Hält die Anmeldung auch nach Erreichen der maximalen Teilnehmerzahl offen. Alle Anmeldungen landen auf einer Warteliste und werden automatisch weiterverfolgt, wenn vorherige Anmeldungen storniert werden.</li>
  <li>Benachrichtigung über Vorrücken von der Warteliste: Diese Benachrichtigung wird gesendet, wenn ein Teilnehmer von der Warteliste vorrückt.</li>
</ul>

## Module

**Die Erweiterung stellt drei neue Event-Module zur Verfügung:**

<ul>
  <li>Anmeldeformular für die Veranstaltung</li>
  <li>Bestätigung der Veranstaltungsregistrierung</li>
  <li>Stornierung der Veranstaltungsregistrierung</li>
  <li>Veranstaltungsregistrierungsliste</li>
</ul>

Alle diese Module sind optional.  
> [!IMPORTANT]
> Das Veranstaltungsregistrierungsformular muss auf derselben Seite wie das Veranstaltungslesermodul eingefügt werden und zeigt das Veranstaltungsregistrierungsformular an, wenn für die Veranstaltung die Registrierung aktiviert ist. Alternativ ist dieses Formular auch als Vorlagenvariable in Veranstaltungsvorlagen verfügbar.

Die Bestätigungs- und Stornierungsformulare können auf anderen Seiten eingefügt werden. In diesem Fall müssen Sie diese Seiten auch in den Einstellungen des Kalenders angeben. Andernfalls wird davon ausgegangen, dass diese Module auch auf der Veranstaltungsleserseite vorhanden sind. Die Module ermöglichen es Ihnen, einen Knoten für detaillierte Inhalte zu definieren. Dieser Inhalt wird angezeigt, wenn eine Veranstaltungsregistrierung erfolgreich bestätigt oder storniert wurde. Sie können auch eine Benachrichtigung auswählen, die nach erfolgreicher Stornierung oder Bestätigung gesendet wird. Wenn Sie weder die Bestätigungs- noch die Stornierungsfunktion benötigen, müssen diese Module nicht erstellt werden.

Mit dem Modul Event-Registrierungsliste können Sie auf der Event-Reader-Seite eine Liste der Registrierungen für das aktuelle Event anzeigen. Die Liste zeigt nur bestätigte Registrierungen, wenn die Registrierungsbestätigung aktiviert ist, und auch keine stornierten Registrierungen. Standardmäßig `mod_event_registration_list` verwendet die Vorlage für jeden Eintrag eine automatisch generierte Beschriftung gemäß der `list.label` Konfiguration des `tl_event_registration` DCA. Die Standardkonfiguration verwendet die Felder `firstname` und `lastname` – wenn Ihr eigenes Formular diese Werte jedoch nicht hat, müssen Sie eine benutzerdefinierte `mod_even_registration_list` Vorlage erstellen und die richtigen Felder entsprechend ausgeben. Alle übermittelten Werte des Registrierungsformulars für jede Registrierung sind unter dem `form_data` Schlüssel verfügbar. 

**Beispiel:**

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


### Template-Variablen

**Die folgenden Vorlagenvariablen stehen in Veranstaltungsvorlagen sowie der Vorlage für das Veranstaltungsanmeldungsformular zur Verfügung:**

- `$this->canRegister`: Ob für diese Veranstaltung eine Anmeldung möglich ist.
- `$this->registrationForm`: Enthält das Registrierungsformular.
- `$this->isFull`: Ob die maximale Anzahl an Anmeldungen für diese Veranstaltung erreicht wurde.
- `$this->isWaitingList`: Ob die maximale Anzahl an Anmeldungen für diese Veranstaltung erreicht wurde und ob eine Warteliste aktiviert ist.
- `$this->registrationCount`: Der aktuelle Registrierungsstand für diese Veranstaltung.
- `$this->reg_min`: Die Mindestanzahl an Anmeldungen für diese Veranstaltung.
- `$this->reg_max`: Die maximale Anzahl an Anmeldungen für diese Veranstaltung.
- `$this->reg_regEnd`: Zeitstempel, nach dem eine Registrierung nicht mehr möglich ist.
- `$this->reg_cancelEnd`: Zeitstempel, nach dem eine Stornierung nicht mehr möglich ist.

### Einfache Token

Innerhalb der Benachrichtigungen sowie der Knoteninhalte der Bestätigungs- und Abbruchmodule stehen folgende einfache Token zur Verfügung:

- `##event_*##`: Alle Variablen des Ereignisses.
- `##reg_*##`: Alle Variablen der Registrierung (dazu gehören alle Formulardaten).
- `##reg_amount##`: Die Anzahl der Personen für eine Registrierung.
- `##reg_count##`: Die jeweilige Registrierungsanzahl.
- `##reg_confirm_url##`: Die URL mit der die Registrierung bestätigt werden kann.
- `##reg_cancel_url##`: Die URL, mit der die Registrierung storniert werden kann.

## Mehrere Sprachen

Diese Erweiterung unterstützt terminal42/contao-changelanguage. Wenn für ein Event ein Hauptevent definiert ist, ist die oben genannte Option nur für das Hauptevent verfügbar. Alle Registrierungen werden immer mit dem Hauptevent verknüpft und für dieses gezählt, sodass die Gesamtzahl der Registrierungen für alle mit dem Hauptevent verknüpften Events gleich ist.

Dies gilt auch für die oben genannten Template-Variablen. Sie verweisen immer auf das Hauptereignis, sofern verfügbar.

## Registrierungen anzeigen und exportieren

Für jede Veranstaltung gibt es ein zusätzliches Icon in der Veranstaltungsliste im Backend.

Hier können Sie die Anmeldungen zu den einzelnen Veranstaltungen auflisten.

Innerhalb der Liste kannst du über das Info-Symbol die Details der Veranstaltungsanmeldung einsehen. Außerdem kannst du hier einige Eigenschaften der Veranstaltung bearbeiten, wie zum Beispiel die Anzahl der Personen für diese Anmeldung und ob sie bestätigt oder storniert wurde.

Die Übersicht bietet Ihnen außerdem die Möglichkeit, die Registrierungen als CSV zu exportieren. Beim Export können Sie das Trennzeichen (entweder `,` oder `;`) konfigurieren und eine Microsoft Excel-kompatible CSV-Datei exportieren.

## Backend-Konfiguration

Die Event-Registrierungsliste im Backend (sowie im Frontend-Modul) verwendet standardmäßig die Felder `firstname` und `lastname`. Wenn Ihr Event-Registrierungsformular diese Felder jedoch nicht hat, möchten Sie möglicherweise die DCA-Konfiguration anpassen, damit im Backend eine Ihren Anforderungen entsprechende Beschriftung angezeigt wird. Wenn Ihr Formular beispielsweise die Felder und verwendet `vorname`, können Sie die Backend-Beschriftungen wie folgt konfigurieren: `nachname` `email`

````
// contao/dca/tl_event_registration.php
$GLOBALS['TL_DCA']['tl_event_registration']['list']['label']['fields'] = ['vorname', 'nachname', 'email'];
$GLOBALS['TL_DCA']['tl_event_registration']['list']['label']['format'] = '%s %s (%s)';
````

Beachten Sie, dass dies auch Auswirkungen auf die Standardbeschriftungen des Front-End-Moduls der Veranstaltungsregistrierungsliste hat .

## Benutzerdefinierte Teilnehmerzahl

Standardmäßig wird bei jeder Registrierung von einer Person ausgegangen. Es ist jedoch auch möglich, dem Besucher, der sich für eine Veranstaltung anmeldet, zu erlauben, die Anzahl der Personen für diese Registrierung selbst festzulegen. Fügen Sie dazu ein neues Textformularfeld (vorzugsweise mit numerischer Validierung) mit dem Feldnamen in das Formular ein `amount`. Dadurch wird die Standardanzahl überschrieben und die Gesamtzahl der Registrierungen wird ebenfalls um diesen Betrag erhöht.


## Mehrfachregistrierungen

Ab der Version 2.2.0 können Sie Besuchern auch die Anmeldung für mehrere Veranstaltungen gleichzeitig ermöglichen. Dafür gibt es nun zwei neue Funktionen:

Ein Front-End-Modul für den Veranstaltungsregistrierungskalender , das ein normales Kalendermodul (genau wie der reguläre Kalender) mit einem Kontrollkästchen für jede Veranstaltung im Kalender rendert.
Ein ausgewähltes Ereignisformularfeld , welches die Auswahl in einem Formular des Formulargenerators anzeigt und später verarbeitet.

**Insgesamt muss Folgendes getan werden, um diese Funktion zu nutzen:**
<ol>
  <li>Erstellen Sie eine neue Benachrichtigung, die für die Registrierung mehrerer Ereignisse verwendet wird (die Token sind dieselben).</li>
  <li>Erstellen Sie ein neues Formular, das für die Registrierung mehrerer Veranstaltungen verwendet wird.</li>
  <li>Fügen Sie in dieses Formular ein ausgewähltes Ereignisformularfeld ein .</li>
  <li>Wählen Sie außerdem die entsprechende Benachrichtigung aus.</li>
  <li>Erstellen Sie eine neue Seite, auf der Sie das neue Anmeldeformular einfügen (alternativ können Sie es auch auf derselben Seite wie den Kalender einfügen).</li>
  <li>Erstellen Sie ein neues Kalendermodul zur Veranstaltungsregistrierung und wählen Sie die zuvor erstellte Seite als Weiterleitungsseite aus (oder keine).</li>
  <li>Fügen Sie dieses Modul in eine neue Seite ein.</li>
</ol>

Wenn Sie diese Seite im Frontend aufrufen, siehst du bei den einzelnen Events, für die die Anmeldung aktiviert ist, Checkboxen. Du kannst auch zwischen den Monaten wechseln – die vorherige Auswahl bleibt dabei erhalten. Sobald du auf „Weiter“ klickst, wirst du zum Anmeldeformular weitergeleitet, wo dir eine Liste der Events angezeigt wird, die für diese Anmeldung ausgewählt wurden.

Nach dem Absenden des Registrierungsformulars wird die Benachrichtigung des Formulars wie gewohnt gesendet. Wenn `##reg_confirm_url##` Ihre Benachrichtigung ein Token enthielt, bestätigt die Bestätigungs-URL automatisch die Registrierungen für alle ausgewählten Veranstaltungen.

## Mitglieder-Registrierungsliste

Zusätzlich zu den oben genannten Funktionen gibt es im Benutzerbereich auch ein neues Frontend-Modul für Veranstaltungsregistrierungen . Dieses Modul listet alle Veranstaltungsregistrierungen des aktuell angemeldeten Frontend-Benutzers auf. Es zeigt auch Links zum Bestätigen (falls zutreffend) oder Abbrechen der Registrierung an.
