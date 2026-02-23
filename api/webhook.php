<?php

// جلوگیری از کش
header("Content-Type: application/json");

$botToken = "8150804033:AAEm8DBauf1TnpwBJQCWhFLt81TrlbzwDeY";

/* ===== دریافت آپدیت ===== */
$rawData = file_get_contents("php://input");
$update = json_decode($rawData, true);

/* ===== دیباگ ===== */
file_put_contents("/tmp/debug.txt", $rawData . "\n\n", FILE_APPEND);

if (!$update || !isset($update["message"])) {
    echo json_encode(["status"=>"no message"]);
    exit;
}

$chat_id = $update["message"]["chat"]["id"];
$user_id = $update["message"]["from"]["id"];
$text = $update["message"]["text"] ?? "";

/* ===== تشخیص referral ===== */
$referrer = null;

if (preg_match('/\/start\s+REF_(\d+)/', $text, $match)) {
    $referrer = $match[1];
}

/* ===== اتصال دیتابیس ===== */
try {
    $pdo = new PDO(
        "mysql:host=sql107.infinityfree.com;dbname=if0_37781627_1;charset=utf8mb4",
        "if0_37781627",
        "Alihaji123",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo json_encode(["db_error"=>$e->getMessage()]);
    exit;
}

/* ===== بررسی کاربر ===== */
$stmt = $pdo->prepare("SELECT telegram_id FROM energy_users WHERE telegram_id=?");
$stmt->execute([$user_id]);

if (!$stmt->fetch()) {

    if ($referrer == $user_id) {
        $referrer = null;
    }

    $pdo->prepare("
        INSERT INTO energy_users
        (telegram_id,total_pnte,last_claim_date,referred_by)
        VALUES (?,?,?,?)
    ")->execute([$user_id,0,'',$referrer]);
}

/* ===== ارسال دکمه Mini App ===== */
$keyboard = [
    "inline_keyboard" => [
        [
            [
                "text" => "⚡ Open Energy App",
                "web_app" => [
                    "url" => "https://t.me/Pinetworkintonbot?start=REF_".$user_id
                ]
            ]
        ]
    ]
];

file_get_contents(
    "https://api.telegram.org/bot$botToken/sendMessage?" .
    http_build_query([
        "chat_id" => $chat_id,
        "text" => "Welcome to PINET Energy ⚡\n\nTap below to start mining:",
        "reply_markup" => json_encode($keyboard)
    ])
);

echo json_encode(["status"=>"ok"]);