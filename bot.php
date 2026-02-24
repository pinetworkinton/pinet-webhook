<?php

$token = getenv("8678079581:AAEY90t413TrmRMytQtAB0FXzKk2LLmmK9s");
$api = "https://api.telegram.org/bot".$token;

$offset = 0;

while (true) {

    $updates = json_decode(file_get_contents($api."/getUpdates?offset=".$offset), true);

    if (!empty($updates["result"])) {

        foreach ($updates["result"] as $update) {

            $offset = $update["update_id"] + 1;

            if (isset($update["message"]["text"])) {

                $chat_id = $update["message"]["chat"]["id"];
                $text = $update["message"]["text"];

                $reply = "😊 " . $text;

                file_get_contents($api."/sendMessage?chat_id=".$chat_id."&text=".urlencode($reply));
            }
        }
    }

    sleep(1);
}