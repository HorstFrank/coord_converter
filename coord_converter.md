# Coordinate Converter

Der Coorinate Converter ist eine PHP-Funktion die der Darstellung und Konvertierung von Geo-Koordinaten dient.

Das Schema dabei ist:

```php
 coord_converter(string $pattern, string $coordinate [,array Parameter])
```

## Benutzung 

Die Konvertierung geschieht dabei über ein Pattern, dass bei der Konvertierung anzugeben ist.

Einfache Anwendung:

```php
coord_converter("hdd°mm.mm'", '56.87654321' );
// E56°52.59'
coord_converter("dd.dddddddd", "E56°52.59'" );
// 56.8765
coord_converter("hdd°mm'ss.sssss\"", '56.87654321' );
// E56°52'35.55556"
coord_converter("dd.dddddddd", "E56°52'35.55556\"" );
// 56.87654321
```

## Benutzung & Besonderheiten

### Das Pattern

#### Zugriff auf Hemisphere

Weil eine Angabe wie zum Beispiel ```56.87654321``` keinen Aufschluss über die Hemisphere Nord/Süd bzw West/Ost Richtung gibt, kann man über ```H``` (Nord/Süd) bzw. ```h``` (Ost/West) der Orientierung steuern.

```php
coord_converter("hdd°mm'ss.sssss\"", '56.87654321' );
// E56°52'35.55556"
coord_converter("hdd°mm'ss.sssss\"", '-56.87654321' );
// W56°52'35.55556"
coord_converter("Hdd°mm'ss.sssss\"", '56.87654321' );
// N56°52'35.55556"
coord_converter("Hdd°mm'ss.sssss\"", '-56.87654321' );
// S56°52'35.55556"
```
Soll in der Ausgabe ein einfaches mathematisches Vorzeichen verwendet werden, (statt N,S,W,E) wird ein (kleines) ```p``` verwendet. Dabei wird bei einer positiven Koordinate das Pluszeichen ```+``` _nicht_ dargestellt. Um ein mögliches positives Vorzeichen zu erzwingen muss im Muster das ```P``` verwendet werden.

```php
coord_converter("ddd.ddddddd", "W11°31'33" );
// 11.5258333
coord_converter("pddd.ddddddd", "W11°31'33" );
// -11.5258333
coord_converter("Pddd.ddddddd", "W11°31'33" );
// -11.5258333
coord_converter("pddd.ddddddd", "E11°31'33" );
// 11.5258333
coord_converter("Pddd.ddddddd", "E11°31'33" );
// +11.5258333
```

#### Zugriff auf Koordinaten

Bestimme Buchstaben geben Zugriff auf die Zahlen einer Geokoordinate.

Grundsätzlich bieten die Buchstaben D, M, S bzw. d, m, s auf die Werte von Grad, Minute und Sekunde. Wobei zu beachten ist, dass dies sowohl Integer als auch Floatanteil einer Zahl betreffen. 

```php
coord_converter("d.ddd / m.mmm / s.sss", '56.12345678' );
//56.123 / 7.407 / 24.444
```

Den ausschließlichen Zugriff auf den Floatanteil einer Zahl bekommt man über F, L, O bzw. f, l, o wobei F der floatanteil von D ist, L der floatanteil von M und O der floatanteil von S.

```php
coord_converter("d / m / s", '56.12345678' );
// 56 / 7 / 24
coord_converter("d.ddd / m.mmm / s.sss", '56.12345678' );
//56.123 / 7.407 / 24.444
coord_converter("fff / lll / ooo", '56.12345678' );
// 123 / 407 / 444
```

#### Mit Großbuchstaben Stellen erzwingen

In vielen Fällen ist eine bestimmte Anzahl an Stellen erwünscht. So ist diese Darstellung allgemein unüblich ```E56°7'24.44"```. Stattdessen wünscht man sich diese Darstellung: ```E056°07'24.44"```. Um diese Darstellung zu erzwingen müssen Großbuchstaben verwendet werden.

```php
coord_converter("hddd°mm'ss.ss\"", '56.12345678' );
// E56°7'24.44"
coord_converter("hDDD°MM'SS.ss\"", '56.12345678' );
// E056°07'24.44"
```

Bei der Angabe von Kleinbuchstaben wird immer die maximale Anzahl an Stellen angegeben. Hat die Zahl weniger Stellen, als im Muster gefordert, werden weniger stellen angezeigt. 

Nullen links der Integer und Rechts vom Floatanteil der Zahl werden also weggekürzt. Aber die Anzahl von Nachkommastellen lassen sich duch Großbuchstaben erzwingen:

```php
coord_converter("hDDD°mm'ss.ssss", '56.123' );
// E056°7'22.8"
coord_converter("hDDD°MM'SS.SSSS\"", '56.123' );
// E056°07'22.8000"
```
Ob beim Integerteil der Zahl nun Großbuchstaben oder Kleinbuchstaben benutzt werden, es wird immer die ermittelte Zahl angezeigt. Die Ziffern werden nie weggekürzt.

```php
coord_converter("d.d", "E11°31'33\"" );
// 11.5
coord_converter("D.D", "E11°31'33\"" );
// 11.5
coord_converter("ddd.ddd", "E11°31'33\"" );
// 11.526
coord_converter("DDD.ddd", "E11°31'33\"" );
// 011.526
```

Durch Großbuchstaben lassen sich spezielle Formatierungen, wie zum Beispiel in einem NMEA-Sentence bewerkstelligen.

```php
coord_converter("DDMM.MMM,H", "N48°7'2.3" );
// 4807.038,N  
coord_converter("DDDMM.MMM,h", "E11°31'" );
// 01131.000,E 
```

## Die Koordinate

Als Koordinate wird ein String im Format D, DM oder DMS erwartet. Wobei, naja so ganz stimmt das nicht. Eine leeres String wird als 0° (D°) interpretiert.

Grundsätzlich gilt aber dass die erste gefundene Zahl als Grad, die Zweite als Minute und die dritte als Sekunde interpretiert wird. Unabhängig von der Suffix der Zahl.

```php
coord_converter("HDD°MM'SS.SS"", "N48°07'02.3" );
// N48°07'02.30"
coord_converter("HDD°MM'SS.SS"", "N48'07°02.3°" );
// N48°07'02.30"
```

Als Trenner zwischen den Zahlen gelten ```°```,```'```,```"``` und ``` ``` (Leerzeichen)

