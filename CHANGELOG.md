# Changelog

[//]: <> (
Types of changes
    Added for new features.
    Changed for changes in existing functionality.
    Deprecated for soon-to-be removed features.
    Removed for now removed features.
    Fixed for any bug fixes.
    Security in case of vulnerabilities.
)

## [1.0.0](https://github.com/pdir/contao-right-of-withdrawal-bundle/tree/1.0.0) – 2026-11-01

Erstes Release.

**Widerruf und Kündigung**

- [Added] Widerruf nach § 356a BGB („Vertrag widerrufen“, „Widerruf bestätigen“)
- [Added] Rücktritt nach § 13a FAGG für Österreich
- [Added] Kündigung nach § 312k BGB („Verträge hier kündigen“, „jetzt kündigen“); bleibt ohne ausgewählte Seite deaktiviert und wird für Österreich nicht angeboten
- [Added] Zugang ohne Login und Abgabe der Erklärung mit dem Klick auf den Bestätigungsbutton, ohne Double-Opt-in, Zusammenfassungsschritt oder Abfrage eines Widerrufsgrunds
- [Added] Prüfung nur der gesetzlichen Angaben; eine unbekannte Bestellnummer oder eine abgelaufene Frist verhindert die Abgabe nicht

**Einbindung**

- [Added] Inhaltselement „Widerruf-/Kündigungsformular“
- [Added] Frontend-Modul „Widerruf-/Kündigungslink“
- [Added] Insert-Tags `{{withdrawal_link}}`, `{{cancellation_link}}`, `{{withdrawal_url}}` und `{{cancellation_url}}`
- [Added] Vorausfüllen des Vertragsfelds über den URL-Parameter `?contract=`, z. B. für einen Link in der Bestellbestätigung
- [Added] Einstellungen je Website-Root für Rechtsraum, Formularseiten, Empfänger, Aufbewahrung und Benachrichtigung

**Bestätigung**

- [Added] Eingangsbestätigung per E-Mail an den Kunden und Kopie an den Betreiber
- [Added] Download der Erklärung als HTML-Datei über einen zeitlich begrenzten Link
- [Added] Erneuter Versand der Bestätigung aus dem Backend

**Nachweis und Backend**

- [Added] Speicherung jeder Erklärung mit laufender Nummer je Website-Root, Serverzeitstempel und verketteter SHA-256-Prüfsumme
- [Added] Konsolenbefehl `pdir:withdrawal:verify` zum Erkennen nachträglicher Änderungen und Lücken
- [Added] Backend-Modul „Widerruf & Kündigung“, in dem sich nur Status und Notizen ändern lassen, mit CSV-Export
- [Added] Warnungen auf der Backend-Startseite bei fehlendem Empfänger und fehlgeschlagenem E-Mail-Versand
- [Added] Täglicher Cronjob, der Erklärungen nach Ablauf der Aufbewahrung zum Jahresende und gehashte IP-Adressen nach 90 Tagen löscht
- [Added] Schutz vor Missbrauch durch Honeypot und Rate-Limit mit gehashter IP-Adresse

**Integrationen und Sprachen**

- [Added] Optionaler Abgleich mit Isotope-Bestellnummer und Rechnungsadresse als Hinweis im Backend
- [Added] Optionaler Versand über das Notification Center mit dem Benachrichtigungstyp `withdrawal_declaration`
- [Added] Texte auf Deutsch und Englisch passend zur Sprache der Website-Root; die Sprache wird mit jeder Erklärung gespeichert und gilt auch für den erneuten Versand und den Download
- [Added] Unterstützung für Contao 5.3 und 6.0
