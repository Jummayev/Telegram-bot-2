<?php
$admin = '1157219338';
$token = '5100879543:AAHBUzKgI95FP_InC4PXzHTYYpFGjnZ3_Iw';
$mybot = 'test00983hheeec_bot';

function bot($method, array $data = [])
{
    global $token;
    $url = 'https://api.telegram.org/bot' . $token . '/' . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$message_id = $message->message_id;
$type = $message->chat->type;
$text = $message->text;
$chat_id = $message->chat->id;
$contact = $message->contact;
$phone_number = $contact->phone_number;
$entities = $message->entities;
$entities_type = $entities[0]->type;
//Database user data
$servername = 'localhost:3306';
$username = 'iduacade_user_db';
$password = '$2i,ou{b$;Qx';
$dbname = 'iduacade_bot';
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// // Create folder
// mkdir('step', 0777);
$step = file_get_contents("step/$chat_id.txt");
//find user
$sql = sprintf('SELECT * FROM users WHERE id=%s', $chat_id);
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    include($user['language'] . '/message.php');

    if ($step == 'language') {
        if ($text == '🇷🇺Русский' | $text == '🇺🇿O\'zbekcha') {
            if ($text == '🇷🇺Русский') {
                $text = 'ru';
            } elseif ($text == '🇺🇿O\'zbekcha') {
                $text = 'uzb';
            }
            $sql = sprintf("UPDATE users SET language='%s' WHERE id=%s", $text, $chat_id);
            $conn->query($sql);
            file_put_contents("step/$chat_id.txt", 'you');

            $sql = sprintf('SELECT * FROM users WHERE id=%s', $chat_id);
            $result = $conn->query($sql);
            $user = $result->fetch_assoc();
            include($user['language'] . '/message.php');

            bot('SendMessage', [
                'chat_id' => $chat_id,
                'text' => $langArray['you'],
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [
                            ['text' => $langArray['youButtonY']],
                            ['text' => $langArray['youButtonJ']]
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true
                ])
            ]);
        } else {
            bot('SendMessage', [
                'chat_id' => $chat_id,
                'text' => 'Выберите язык/Tilni tanlang:',
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [
                            ['text' => '🇷🇺Русский'],
                            ['text' => "🇺🇿O'zbekcha"]
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true
                ])
            ]);
        }
    } else if ($step == 'you') {
        if ($text == '🏢Юр. лицо' | $text == '👤Физ. лицо' | $text == '🏢Yuridik shaxs' | $text == '👤Jismoniy shaxs') {
            if ($text == '🏢Юр. лицо' | $text == '🏢Yuridik shaxs') {
                $text = 1;
                $status = "login";
            } elseif ($text == '👤Физ. лицо' | $text == '👤Jismoniy shaxs') {
                $text = 0;
                $status = 'fullName';
            }
            $sql = sprintf("UPDATE users SET legal_entity='%s' WHERE id=%s", $text, $chat_id);
            $conn->query($sql);
            file_put_contents("step/$chat_id.txt", $status);
            bot('SendMessage', [
                'chat_id' => $chat_id,
                'text' => $status == 'login' ? $langArray['login'] : $langArray['fullName'],
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'remove_keyboard' => true,
                ]),
            ]);
        } else {
            bot('SendMessage', [
                'chat_id' => $chat_id,
                'text' => $langArray['you'],
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [
                            ['text' => $langArray['youButtonY']],
                            ['text' => $langArray['youButtonJ']]
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true
                ])
            ]);
        }

    }else if ($step == 'login') {
        $sql = sprintf("UPDATE users SET login='%s' WHERE id=%s", $text, $chat_id);
        $conn->query($sql);
        file_put_contents("step/$chat_id.txt", "password");

        bot('SendMessage', [
            'chat_id' => $chat_id,
            'text' => $langArray['password'],
            'parse_mode' => 'HTML',
        ]);
    }
 else if ($step == 'password') {
     $sql = sprintf("UPDATE users SET parol='%s' WHERE id=%s", $text, $chat_id);
     $conn->query($sql);
     file_put_contents("step/$chat_id.txt", 'fullName');
     bot('SendMessage', [
         'chat_id' => $chat_id,
         'text' => $langArray['fullName'],
         'parse_mode' => 'HTML',
         'reply_markup' => json_encode([
             'remove_keyboard' => true,
         ]),
     ]);
    } else if ($step == 'fullName') {
    $sql = sprintf("UPDATE users SET full_name='%s' WHERE id=%s", $text, $chat_id);
    $conn->query($sql);
    file_put_contents("step/$chat_id.txt", 'phoneNumber');
    bot('SendMessage', [
        'chat_id' => $chat_id,
        'text' => $langArray['phoneNumber'],
        'parse_mode' => 'HTML',
        'reply_markup' => json_encode([
            'keyboard' => [
                [
                    [
                        'text' => $langArray['phoneNumberButton'],
                        'request_contact' => true,
                    ]
                ],
            ],
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        ])
    ]);
}
    else if ($step == 'phoneNumber') {
        if ($entities and $entities_type == 'phone_number') {
            $phone_number = $text;
        }
        if ($phone_number) {
            $sql = sprintf("UPDATE users SET phone='%s' WHERE id=%s", $phone_number, $chat_id);
            $conn->query($sql);
            file_put_contents("step/$chat_id.txt", 'successFully');
            $sql = sprintf('SELECT * FROM users WHERE id=%s', $chat_id);
            $result = $conn->query($sql);
            $user = $result->fetch_assoc();

            $resultMessageRuYu = '
Ваши данные:
🏷ФИО: ' . $user['full_name'] . '
' . $langArray['youButtonY'] . '
🔐Login: ' . $user['login'] . '
🔐Password: ' . $user['parol'] . '
📞Телефон: ' . $user['phone'] . '
Ознакомьтесь с Офертой, наш администратор с вами свяжется
';
            $resultMessageUzbYu = "
    Ma'lumotlaringiz :
    🏷F.I.SH:  " . $user['full_name'] . '
    ' . $langArray['youButtonY'] . '
    🔐Login:  ' . $user['login'] . '
    🔐Parol:  ' . $user['parol'] . '
    📞Telefon:  ' . $user['phone'] . "
Taklifni ko'rib chiqing, bizning administratorimiz siz bilan bog'lanadi
";

            $resultMessageRuJi = '
Ваши данные:
🏷ФИО: ' . $user['full_name'] . '
' . $langArray['youButtonJ'] . '
📞Телефон: ' . $user['phone'] . '
Ознакомьтесь с Офертой, наш администратор с вами свяжется
';
            $resultMessageUzbJi = "
Ma'lumotlaringiz :
    🏷F.I.SH: " . $user['full_name'] . '
    ' . $langArray['youButtonJ'] . '
    📞Telefon:  ' . $user['phone'] . "
Taklifni ko'rib chiqing, bizning administratorimiz siz bilan bog'lanadi
";
            $resultMessage = [
                'yu' => [
                    'ru' => $resultMessageRuJi,
                    'uzb' => $resultMessageUzbJi
                ],
                'ji' => [
                    'ru' => $resultMessageRuYu,
                    'uzb' => $resultMessageUzbYu
                ]
            ];


            bot('SendMessage', [
                'chat_id' => $chat_id,
                'text' => $user['legal_entity'] == 0 ? $resultMessage['yu'][$user['language']] : $resultMessage['ji'][$user['language']],
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'remove_keyboard' => true,
                ]),
            ]);
        } else {
            bot('SendMessage', [
                'chat_id' => $chat_id,
                'text' => $langArray['phoneNotNumber'] . '<code>' . json_encode($message) . '</code>',
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'keyboard' => [
                        [
                            [
                                'text' => $langArray['phoneNumberButton'],
                                'request_contact' => true,
                            ]
                        ],
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true
                ])
            ]);
        }
    }else if ($step == 'successFully') {
        bot('SendMessage', [
            'chat_id' => $chat_id,
            'text' => $langArray['start'],
            'parse_mode' => 'HTML',
        ]);
    }


} else {
    $sql = sprintf('INSERT INTO users (id) VALUES (%s)', $chat_id);
    $result = $conn->query($sql);
    file_put_contents("step/$chat_id.txt", 'language');
    bot('SendMessage', [
        'chat_id' => $chat_id,
        'text' => 'Выберите язык/Tilni tanlang:',
        'reply_markup' => json_encode([
            'keyboard' => [
                [
                    ['text' => '🇷🇺Русский'],
                    ['text' => "🇺🇿O'zbekcha"]
                ],
            ],
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        ])
    ]);
}


