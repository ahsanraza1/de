<?php

$db_file = "db.json";

$db_data = [
    "bag" => []
];


/*
|--------------------------------------------------------------------------
| Load database
|--------------------------------------------------------------------------
*/

if (!file_exists($db_file)) {

    file_put_contents(
        $db_file,
        json_encode(
            $db_data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );

} else {

    $content = file_get_contents($db_file);
    $decoded = json_decode($content, true);

    if (is_array($decoded)) {
        $db_data = $decoded;
    }

    if (!isset($db_data["bag"]) || !is_array($db_data["bag"])) {

        $db_data["bag"] = [];

        file_put_contents(
            $db_file,
            json_encode(
                $db_data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
    }
}


/*
|--------------------------------------------------------------------------
| Save database
|--------------------------------------------------------------------------
*/

function save_data($data)
{
    global $db_file;

    file_put_contents(
        $db_file,
        json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
    );
}


/*
|--------------------------------------------------------------------------
| Normalize text
|--------------------------------------------------------------------------
*/

function normalize($text)
{
    $text = trim($text);

    if (function_exists("mb_strtolower")) {
        return mb_strtolower($text, "UTF-8");
    }

    return strtolower($text);
}


/*
|--------------------------------------------------------------------------
| Add new word
|--------------------------------------------------------------------------
*/

function add_new()
{
    global $db_data;

    $newDe = trim(readline("> New German Word : "));
    $newEn = trim(readline("> English : "));

    if ($newDe === "" || $newEn === "") {
        echo "German word and English word cannot be empty.\n";
        return;
    }

    foreach ($db_data["bag"] as $item) {

        if (normalize($item["de"]) === normalize($newDe)) {

            echo "Already Exists.\n";
            return;
        }
    }

    $db_data["bag"][] = [
        "de" => $newDe,
        "en" => $newEn
    ];

    save_data($db_data);

    echo "Added: {$newDe} = {$newEn}\n";
}


/*
|--------------------------------------------------------------------------
| List words
|--------------------------------------------------------------------------
*/

function list_words()
{
    global $db_data;

    if (count($db_data["bag"]) === 0) {

        echo "Bag is empty.\n";
        return;
    }

    echo "\n";
    echo "========== WORDS ==========\n";

    foreach ($db_data["bag"] as $index => $item) {

        $number = $index + 1;

        echo "{$number}. ";
        echo $item["de"];
        echo " = ";
        echo $item["en"];
        echo "\n";
    }

    echo "===========================\n";
    echo "Total: " . count($db_data["bag"]) . "\n\n";
}


/*
|--------------------------------------------------------------------------
| Edit word
|--------------------------------------------------------------------------
*/

function edit_word()
{
    global $db_data;

    if (count($db_data["bag"]) === 0) {

        echo "Bag is empty.\n";
        return;
    }

    /*
     * Show words first
     */
    list_words();

    $input = trim(readline("> Word number to edit : "));

    if (!ctype_digit($input)) {

        echo "Please enter a valid number.\n";
        return;
    }

    $index = (int)$input - 1;

    if (!isset($db_data["bag"][$index])) {

        echo "Word not found.\n";
        return;
    }

    $word = $db_data["bag"][$index];

    echo "\n";
    echo "Editing:\n";
    echo "German  : {$word["de"]}\n";
    echo "English : {$word["en"]}\n";
    echo "\n";

    /*
     * Empty input means keep the old value
     */
    $newDe = trim(
        readline("> German [{$word["de"]}] : ")
    );

    $newEn = trim(
        readline("> English [{$word["en"]}] : ")
    );

    if ($newDe !== "") {
        $db_data["bag"][$index]["de"] = $newDe;
    }

    if ($newEn !== "") {
        $db_data["bag"][$index]["en"] = $newEn;
    }

    save_data($db_data);

    echo "\nUpdated successfully!\n";

    echo $db_data["bag"][$index]["de"];
    echo " = ";
    echo $db_data["bag"][$index]["en"];
    echo "\n";
}


/*
|--------------------------------------------------------------------------
| Exam
|--------------------------------------------------------------------------
*/

function exam()
{
    global $db_data;

    if (count($db_data["bag"]) === 0) {

        echo "Your bag is empty. Add some words first.\n";
        return;
    }

    echo "\n";
    echo "========== EXAM ==========\n";
    echo "1. German -> English\n";
    echo "2. English -> German\n";
    echo "3. Random\n";

    $mode = trim(readline("> Choose mode : "));

    if (!in_array($mode, ["1", "2", "3"])) {

        echo "Invalid choice.\n";
        return;
    }

    /*
     * Copy the bag.
     *
     * shuffle() changes the array, so we don't want
     * to modify the actual database order.
     */
    $all = $db_data["bag"];

    shuffle($all);

    $correct = 0;
    $wrong = 0;

    $results = [];

    echo "\n";
    echo "Starting exam...\n";
    echo "Words: " . count($all) . "\n";
    echo "===========================\n\n";


    foreach ($all as $item) {

        /*
         * Determine exam direction
         */

        if ($mode === "1") {

            // German -> English

            $question = $item["de"];
            $correctAnswer = $item["en"];
            $direction = "DE -> EN";

        } elseif ($mode === "2") {

            // English -> German

            $question = $item["en"];
            $correctAnswer = $item["de"];
            $direction = "EN -> DE";

        } else {

            // Random direction

            if (rand(0, 1) === 0) {

                $question = $item["de"];
                $correctAnswer = $item["en"];
                $direction = "DE -> EN";

            } else {

                $question = $item["en"];
                $correctAnswer = $item["de"];
                $direction = "EN -> DE";
            }
        }


        /*
         * Ask question
         */

        echo "[{$direction}]\n";
        echo "> {$question}\n";

        $userAnswer = trim(
            readline("Answer: ")
        );


        /*
         * Check answer
         */

        if (
            normalize($userAnswer)
            ===
            normalize($correctAnswer)
        ) {

            echo "✅ Correct!\n";

            $correct++;

            $results[] = [
                "question" => $question,
                "your_answer" => $userAnswer,
                "correct_answer" => $correctAnswer,
                "correct" => true
            ];

        } else {

            echo "❌ Wrong!\n";
            echo "   Correct answer: {$correctAnswer}\n";

            $wrong++;

            $results[] = [
                "question" => $question,
                "your_answer" => $userAnswer,
                "correct_answer" => $correctAnswer,
                "correct" => false
            ];
        }

        echo "\n";
    }


    /*
     * Results
     */

    $total = count($all);

    $percentage = $total > 0
        ? round(($correct / $total) * 100)
        : 0;


    echo "\n";
    echo "================================\n";
    echo "             RESULTS\n";
    echo "================================\n";

    echo "Total   : {$total}\n";
    echo "Correct : {$correct}\n";
    echo "Wrong   : {$wrong}\n";
    echo "Score   : {$percentage}%\n";

    echo "================================\n";


    /*
     * Wrong answers
     */

    if ($wrong > 0) {

        echo "\n";
        echo "========== WRONG ANSWERS ==========\n";

        foreach ($results as $result) {

            if (!$result["correct"]) {

                echo "\n";
                echo "Question : {$result["question"]}\n";
                echo "You said : {$result["your_answer"]}\n";
                echo "Correct  : {$result["correct_answer"]}\n";
            }
        }

        echo "\n===================================\n";
    }

    echo "\n";
}


/*
|--------------------------------------------------------------------------
| Main CLI
|--------------------------------------------------------------------------
*/

echo "\n";
echo "==============================\n";
echo "     German Vocabulary App\n";
echo "==============================\n";

echo "Commands:\n";
echo "  add   - Add a new word\n";
echo "  edit  - Edit an existing word\n";
echo "  exam  - Start an exam\n";
echo "  list  - List all words\n";
echo "  exit  - Exit program\n";
echo "\n";


while (true) {

    $inp = trim(readline("> "));

    switch ($inp) {

        case "add":
            add_new();
            break;

        case "edit":
            edit_word();
            break;

        case "exam":
            exam();
            break;

        case "list":
            list_words();
            break;

        case "exit":
            echo "Goodbye!\n";
            exit;

        case "":
            break;

        default:
            echo "Unknown command.\n";
            echo "Available: add, edit, exam, list, exit\n";
            break;
    }
}

?>
