Aplikacja opiera się na jednym kontrolerze (ProductController), jednym modelu (Product) oraz jednym serwisie (OcrAiParser).
Główny widok znajduje się pod adresem: products
Aby mieć możliwość wypróbowania wszytskich funkcji należy dodać HF_API_KEY= w pliku .env


Model produktu zawiera parametry takie jak:
'name'
'description'
'price'
'image'
'ocr_text' - tekst pobierany ze zdjęcia
'color' i 'type' - wyodrębiane przez agenta na podstawie 'ocr_text' i zapisywane do bazy w momencie wyświetlenia pojedynczego produktu

Kontroler daje użytkownikowi dodawania, usuwania, edytowania oraz zobaczenia szczegółów produktów.

Wykorzystałam Tesseract wywoływany w PHP - jest on wykorzystany w funkcjach: store() i update()
- jeśli przesłany jest plik zostaje zapisany w storage/app/public/products
- ścieżka zapisywana jest do bazy
- tworzony jest tymczasowy plik $outputFile
- uruchamiany jest program Tesseract OCR (aby go uruchomić podajemy ścieżkę do obrazu, ścieżkę do tymczasowego pliku, parametr języka u mnie polski i ścieżka do danych językowych)
- jeśli wywołanie zakończy się sukcesem i istnieje plik z wynikiem, tekst ze zdjęcia zapisywany jest do bazy
- jeśli wywołanie nie zakończy się sukcesem do zmiennej przypisany jest null.

Do stworzenia agenta AI wykorzystałam Hugging Face:
- należało założyć konto, a następnie wygenerować token, który należy dodać do pliku .env
- w stworzonym przeze mnie serwisie są dwie funkcje jedna publiczna parse(), która łączy się z api i wysyła prompt z zapytaniem o json z podanego tekstu. Funkcja extractJson(string $text) jest prywatna i jest zabezpieczeniem przed zwróceniem przez agenta niepoprawnego jsona, dodaje cudzysłowy wokół klucza i wartości, a następnie dekoduje JSON do tablicy lub null.   
- obiekt tworzony jest w metodzie show() w ProductController i tam następuje próba uzyskania i przypisania wartości color i type z wartości ocr_text, jeśli nie uda się za pomocą AI, istnieje jeszcze próba dopasowania tekstu do przygotowanego zbioru parametrów (tak, aby prawdopodobieństwo pustych wartości było jak najmniejsze).


