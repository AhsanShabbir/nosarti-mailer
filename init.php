<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'env.php';

use App\NosratiMailer;

//using phpmailer

// $mailer = new NosratiMailer('phpmailer');
// $mailer->to([['devahsanonline@gmail.com', 'Ahsan Shabbir'], ['devahsan.online@gmail.com', 'Ahsan Shabbir 2']]);
// $mailer->from('devahsanonline@gmail', 'Ahsan Shabbir');
// $mailer->subject('Test Email from PHPMailer');
// $mailer->body('<html><body><h1>Hello World!</h1></body><html>', true);
// $mailer->attach('files/nosrati.png');
// $mailer->bcc([['devahsan.online@gmail', 'Ahsan 1']]);
// $mailer->cc([['d.e.v.ahsanonline@gmail', 'CCTest User'], ['d.e.vahsan.online@gmail', 'CC Test User 2']]);
// $mailer->altBody('Hello World! Alt Body');
// echo json_encode($mailer->send());


//ussing sendgrid

// $mailer = new NosratiMailer('sendgrid');
// $mailer->to([['devahsanonline@gmail.com', 'Ahsan Shabbir']]);
// $mailer->from('AhsanMShabbir@gmail.com', 'Ahsan Shabbir');
// $mailer->subject('Test Email from SendGrid');
// $mailer->body('<html><body><h1>Hello World!</h1></body><html>', true);
// $mailer->attach('files/nosrati.png');
// $mailer->bcc([['devahsan.online@gmail.com', 'Ahsan 1']]);
// $mailer->cc([['d.e.v.ahsanonline@gmail.com', 'CCTest User']]);
// $mailer->altBody('Hello World! Alt Body');
// echo json_encode($mailer->send());




//using mailgun
// $mailer = new NosratiMailer('mailgun');
// $mailer->to(['devahsanonline@gmail.com' => 'Ahsan Shabbir']);
// $mailer->from( 'postmaster@sandbox556fb0d4578e4122bc5231235a95664b.mailgun.org', 'Mailgun Sandbox');
// $mailer->subject('Test Email from Mail Gun');
// $mailer->body('<html><body><h1>Hello World!</h1></body><html>', true);
// $mailer->attach('files/nosrati.png');
// $mailer->bcc([['devahsan.online@gmail.com', 'Ahsan 1']]);
// $mailer->cc([['d.e.v.ahsanonline@gmail.com', 'CCTest User'], ['d.e.vahsan.online@gmail.com', 'CC Test User 2']]);
// $mailer->altBody('Hello World! Alt Body');
// echo json_encode($mailer->send());
