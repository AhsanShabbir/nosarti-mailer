<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'env.php';

use App\NosratiMailer;

$mailer = new NosratiMailer();
$mailer->to(['devahsanonline@gmail.com' => 'Ahsan Shabbir']);
$mailer->from("ahsanmshabbir@gmail.com", "Ahsan Shabbir");
$mailer->subject('Test Email');
$mailer->body('<html><body><h1>Hello World!</h1></body><html>', true);
// $mailer->body('Hello World!', false);
$mailer->attach('files/nosrati.png');
$mailer->bcc([['devahsan.online@gmail.com', 'Ahsan 1']]);
$mailer->cc([['d.e.v.ahsanonline@gmail.com', 'CCTest User'], ['d.e.vahsan.online@gmail.com', 'CC Test User 2']]);
$mailer->altBody('Hello World!');
$mailer->send();
