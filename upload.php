<?php

function returnWithMessage($message)
{
    session_start();
    $_SESSION['error'] = $message;
    header("Location: index.php");
    die();
}

function mixWord($word)
{
    //word string to array of word characters
    $wordSigns = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);

    //get first and last characters from word
    $firstLetter = array_shift($wordSigns);
    $lastLetter = array_pop($wordSigns);

    //add punctuation to last letter if exists at the end of word
    if (preg_match('/[.,:?!-]/', $lastLetter)) {
        $lastLetter = array_pop($wordSigns) . $lastLetter;
    }

    //shuffle middle characters
    shuffle($wordSigns);

    $mixedWord = $firstLetter . join("", $wordSigns) . $lastLetter;

    return $mixedWord;
}

function handleLineOfText($inputTextLine)
{
    //remove multiple whitespaces from text line
    $inputTextLine = preg_replace('/\s+/', ' ', $inputTextLine);

    //text to single words
    $words = explode(" ", $inputTextLine);

    $mixedWords = [];

    //mix words from line
    foreach ($words as $word) {
        //mix word if contains more than 3 characters
        array_push($mixedWords, (strlen($word) > 3) ? mixWord($word) : $word);
    }

    return implode(" ", $mixedWords);
}

function createOutputFile($inputFile, $outputText)
{
    //create output file name
    $outputFileName = basename($inputFile['name'], ".txt") . '_mixed.txt';

    //make copy of input file
    copy($inputFile['tmp_name'], $outputFileName);

    //handle output file
    $outputFile = fopen($outputFileName, "w") or die("Unable to open file!");

    //write output text to file
    fwrite($outputFile, $outputText);
    fclose($outputFile);

    //output file headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($outputFileName) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($outputFileName));
    flush();
    readfile($outputFileName);
    die();
}

//check if upload form submitted
if (isset($_POST['uploadSubmit'])) {

    //check if file uploaded
    if (is_uploaded_file($_FILES['inputFile']['tmp_name'])) {

        //check if txt file uploaded
        if ($_FILES['inputFile']['type'] == 'text/plain') {
            //get uploaded file
            $inputFile = $_FILES['inputFile'];

            //get text lines from file
            $inputTextLines = [];
            $fp = fopen($inputFile['tmp_name'], 'r');
            while (($line = fgets($fp)) !== false) {
                array_push($inputTextLines, $line);
            }

            fclose($fp);

            //mix text from lines creating output text
            $outputText = '';
            foreach ($inputTextLines as $inputTextLine) {
                //add new lines of output text
                $outputText .= handleLineOfText($inputTextLine) . PHP_EOL;
            }

            //create output file and write mixed text
            createOutputFile($inputFile, $outputText);
        } else {
            //not txt file uploaded
            returnWithMessage('Dodaj plik tekstowy');
        }

    } else {
        //no file uploaded
        returnWithMessage('Dodaj plik');
    }
} else {
    //form not submittet
    returnWithMessage('Musisz wypełnić formularz');
}

?>




