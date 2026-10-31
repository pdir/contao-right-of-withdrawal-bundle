# Contao Right of Withdrawal Bundle

Elektronische Widerrufs- und Kündigungsfunktion für Contao 5 und 6.

| Funktion | Rechtsgrundlage | Einstieg | Abschluss |
|---|---|---|---|
| Widerruf (Deutschland) | § 356a BGB | „Vertrag widerrufen“ | „Widerruf bestätigen“ |
| Rücktritt (Österreich) | § 13a FAGG | „Vertrag widerrufen“ | „Widerruf bestätigen“ |
| Kündigung (Deutschland) | § 312k BGB | „Verträge hier kündigen“ | „jetzt kündigen“ |

Das Bundle stellt die technische Funktion bereit und ist keine Rechtsberatung. Ob und für welche Verträge die Pflichten gelten, entscheidet der Betreiber.

## Ablauf

1. Der Link im Footer führt ohne Login auf die Formularseite.
2. Das Formular enthält nur die gesetzlichen Angaben und den Bestätigungsbutton. Mit dem Klick ist die Erklärung abgegeben.

Danach wird die Erklärung gespeichert, die Eingangsbestätigung geht per E-Mail raus und der Betreiber erhält eine Kopie. Es gibt bewusst keinen Double-Opt-in, keinen Zusammenfassungsschritt und keine Abfrage eines Widerrufsgrunds.

Eine unbekannte Bestellnummer oder eine abgelaufene Frist verhindert die Abgabe nie. Die Prüfung liegt beim Betreiber.

## Einrichtung

```bash
composer require pdir/contao-right-of-withdrawal-bundle
php vendor/bin/contao-console contao:migrate
```

1. Seite anlegen, z. B. „Vertrag widerrufen“, und dort das Inhaltselement **Widerruf-/Kündigungsformular** einfügen.
2. In der **Website-Root** unter „Widerruf & Kündigung“ Rechtsraum, Empfänger, Aufbewahrung und Formularseite wählen.
3. Das Frontend-Modul **Widerruf-/Kündigungslink** im Seitenlayout in den Footer aller Seiten einbinden. Alternativ das Insert-Tag verwenden.
4. Widerrufsbelehrung und Datenschutzerklärung anpassen.
5. Einmal testen: Formular absenden und die Eingangsbestätigung prüfen.

Die Kündigungsfunktion ist nur nötig, wenn online Dauerschuldverhältnisse (z. B. Abos) geschlossen werden. Ohne ausgewählte Seite bleibt sie deaktiviert. Für Österreich wird sie nicht angeboten.

### Insert-Tags

- `{{withdrawal_link}}`, `{{cancellation_link}}`: fertiger Link
- `{{withdrawal_url}}`, `{{cancellation_url}}`: nur die URL

Mit `?contract=12345` an der URL wird das Vertragsfeld vorausgefüllt, z. B. für einen Link in der Bestellbestätigung.

## Nachweis

Erklärungen liegen unter **Inhalte → Widerruf & Kündigung**. Dort lassen sich nur Status und Notizen ändern, außerdem gibt es einen CSV-Export und den erneuten Versand der Bestätigung.

Jede Erklärung erhält je Website-Root eine laufende Nummer, einen Serverzeitstempel und eine mit dem Vorgänger verkettete SHA-256-Prüfsumme. Nachträgliche Änderungen oder Lücken zeigt:

```bash
php vendor/bin/contao-console pdir:withdrawal:verify
```

Das macht Änderungen sichtbar, verhindert aber keine Manipulation durch jemanden mit Datenbankzugriff, der die ganze Kette neu berechnet.

Ein täglicher Cronjob löscht Erklärungen nach Ablauf der Aufbewahrung zum Jahresende und gehashte IP-Adressen nach 90 Tagen. Der Contao-Cron muss dafür laufen.

## Optionale Integrationen

- **Isotope:** Wenn die Tabellen vorhanden sind, wird das Vertragsfeld mit der Bestellnummer (`document_number`) und die E-Mail mit der Rechnungsadresse abgeglichen. Das Ergebnis ist nur ein Hinweis im Backend.
- **Notification Center:** Wird in der Website-Root eine Benachrichtigung vom Typ `withdrawal_declaration` gewählt, ersetzt sie die eingebauten E-Mails. Die Nachricht muss dann selbst alle Pflichtangaben enthalten: Inhalt der Erklärung sowie Datum und Uhrzeit des Eingangs.

## Anpassen

Das Aussehen lässt sich über die Twig-Templates `frontend_module/withdrawal_link` und `content_element/withdrawal_form` ändern. Beim Überschreiben eines Templates oder einer Übersetzung trägt der Betreiber die Verantwortung für den Wortlaut.

## Mehrsprachigkeit

Die Sprache ergibt sich aus der Sprache der Website-Root. Enthalten sind Deutsch und Englisch, die Texte liegen in `translations/pdir_withdrawal.<sprache>.yaml`. Weitere Sprachen lassen sich über eine Datei `translations/pdir_withdrawal.<sprache>.yaml` im Projekt ergänzen. Fehlt eine Sprache, greift die Fallback-Sprache von Symfony (in Contao standardmäßig die `default_locale`).

| Funktion | Einstieg (de) | Abschluss (de) | Einstieg (en) | Abschluss (en) |
|---|---|---|---|---|
| Widerruf/Rücktritt | „Vertrag widerrufen“ | „Widerruf bestätigen“ | „Withdraw from contract here“ | „Confirm withdrawal“ |
| Kündigung | „Verträge hier kündigen“ | „jetzt kündigen“ | „Cancel contracts here“ | „Cancel now“ |

Die deutschen Beschriftungen sind der gesetzliche Wortlaut. Die englischen Widerrufstexte folgen Art. 11a der Richtlinie 2011/83/EU, die englischen Kündigungstexte sind eine Übersetzung ohne gesetzliche Vorgabe.

Jede Erklärung speichert ihre Sprache und ist Teil der Prüfsumme. Eingangsbestätigung, Kopie an den Betreiber, erneuter Versand und Download erscheinen immer in dieser Sprache. Das Backend folgt der Sprache des Benutzers.

## Lizenz

LGPL-3.0-or-later
