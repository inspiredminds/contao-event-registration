# Contao Event Registrierung

Diese Contao Erweiterung ermöglicht die Registrerung (Buchung) von Events.

## Anwendung

Nach der Installation können (einzelne) Events zur Registrierung (Buchung) freigegeben werden. Die Aktivierung wird in den Einstellungen des Events vorgenommen.

![Registrierung erlauben](images/registrierung-erlauben.png)

### Template Variablen

Die folgenden Vorlagenvariablen stehen in Veranstaltungsvorlagen sowie der Vorlage für das Veranstaltungsanmeldungsformular zur Verfügung:

- `$this->canRegister`: 
- `$this->registrationForm`: 
- `$this->isFull`:
- `$this->isWaitingList`: 
- `$this->registrationCount`:
- `$this->reg_min`: 
- `$this->reg_max`: 
- `$this->reg_regEnd`: 
- `$this->reg_cancelEnd`: 
