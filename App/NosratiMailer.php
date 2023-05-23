<?php

namespace App;

use App\Traits\HasResponses;
use Mailgun\Mailgun;
use PHPMailer\PHPMailer\PHPMailer;
use SendGrid;

class NosratiMailer
{

    use HasResponses;
    private $fromName;
    private $fromAddress;
    private $to  = [];
    private $subject;
    private $body = '';
    private $altBody = '';
    private $isHTML = true;
    private $cc  = [];
    private $bcc = [];
    private $attachments = [];
    private $driver;


    public function __construct($driver = 'phpmailer')
    {
        $this->driver = $driver;
        $this->fromAddress = getenv('MAIL_FROM_ADDRESS');
        $this->fromName = getenv('MAIL_FROM_NAME');
    }


    public function from($address, $name)
    {
        $this->fromAddress = $address;
        $this->fromName = $name;
        return $this;
    }

    public function to(array $to)
    {
        $this->to = $to;
        return $this;
    }

    public function cc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    public function bcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function body($body, $isHTML = true)
    {
        $this->body = $body;
        $this->isHTML = $isHTML;
        return $this;
    }

    public function altBody($altBody)
    {
        $this->altBody = $altBody;
        return $this;
    }

    

    public function attach($file)
    {
        //allow pdf, docx, doc, xls, csv, png
        $allowed = ['pdf', 'docx', 'doc', 'xls', 'csv', 'png', 'jpg', 'jpeg'];
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if (!in_array($ext, $allowed)) {
            throw new \Exception('Only pdf, docx, doc, xls, csv, png files are allowed');
        }
        $this->attachments[] = $file;
        return $this;
    }

    public function setDriver($driver)
    {
        $this->driver = $driver;
        return $this;
    }

    public function send()
    {
        try {
            //validate data
            $this->validate();
            if ($this->driver == 'phpmailer') {
                return $this->sendWithPHPMailer();
            }
            if($this->driver == 'sendgrid'){
               return $this->sendWithSendGrid();
            }
            if($this->driver == 'mailgun'){
               return $this->sendWithMailGun();
            }

        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    private function validate()
    {
        //check if subject, from, to, body are not empty
        if (empty($this->subject) || empty($this->fromAddress) || empty($this->fromName) || empty($this->to) || empty($this->body) || empty($this->altBody)) {
            throw new \Exception('Subject, From, To, Body, AltBody are required');
        }
    }

    /**Send Email with PHP Mailer */
    private function sendWithPHPMailer()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');
        $mail->SMTPSecure = getenv('MAIL_ENCRYPTION');
        $mail->Port = getenv('MAIL_PORT');

        $mail->setFrom($this->fromAddress, $this->fromName);

        foreach ($this->to as $recipient) {
            $mail->addAddress($recipient[0], $recipient[1]);
        }

        
        foreach ($this->cc as $email) {

            $address =  $email[0];
            $name = $email[1]; 
            $mail->addCC($address, $name);
        }
        
        foreach ($this->bcc as $email) {
            $address =  $email[0];
            $name = $email[1]; 
            $mail->addBCC($address, $name);
        }

        foreach ($this->attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->Subject = $this->subject;
        $mail->Body = $this->body;
        $mail->AltBody = $this->altBody;
        if ($this->isHTML) {
            $mail->isHTML(true);
        }

        if (!$mail->send()) {
            return $this->error($mail->ErrorInfo);
        }
        return $this->success('Email sent successfully');
    }

    /**
     * Send with MailGun
     *
     * @return void
     */
    private function sendWithMailGun(){

        //send email 
        $mg = Mailgun::create(getenv('MAILGUN_KEY'));
        $domain = getenv('MAILGUN_DOMAIN');


        $data = [
            'from'    => $this->mgFormatSingle($this->fromName, $this->fromAddress),
            'to'      => $this->mgFormat($this->to),
            'subject' => $this->subject,
            'text'    => $this->altBody,
            'html'    => $this->isHTML ? $this->body : null,
            'attachment' => $this->attachments ? $this->mgPrepareAttachments($this->attachments) : null
        ];
        
        $mg->messages()->send($domain, $data );

        return $this->success('Email sent successfully with MailGun');

    }

    /**
     * Undocumented function
     *
     * @param array $email
     * @return void
     */
    private function mgformat(array $email){
        $formatted = [];
        foreach($email as $address => $name){
          $formatted[] = $this->mgFormatSingle($name, $address);
        }

        return implode(',', $formatted);
    }

    /**
     * Undocumented function
     *
     * @param [type] $name
     * @param [type] $address
     * @return void
     */
    private function mgFormatSingle($name, $address){
        return $name . ' <' . $address . '>';
    }

    
    private function mgPrepareAttachments($attachments)
    {
        $files = [];

        foreach ($attachments as $attachment) {
            $files[] = [
                'filePath' => $attachment
            ];
        }

        return $files;
    }
    /**
     * Send Email with SendGrid
     *
     * @return void
     */
    private function sendWithSendGrid()
    {
        $mail = new \SendGrid\Mail\Mail();
        $mail->setFrom($this->fromAddress, $this->fromName);
        
        $mail->setSubject($this->subject);
        
        foreach ($this->to as $email) {
            $address =  $email[0];
            $name = $email[1];
            $mail->addTo($address, $name);
        }
    
        foreach ($this->cc as $email) {
            $address =  $email[0];
            $name = $email[1];
            $mail->addCc($address, $name);
        }
        
        foreach ($this->bcc as $email) {
            $address =  $email[0];
            $name = $email[1]; 
            $mail->addBcc($address, $name);
        }
        
        $mail->addContent($this->isHTML ? "text/html" : "text/plain", $this->body);
        $mail->addContent("text/plain", $this->altBody);

        //add attachment with dynamic name and content type
        foreach ($this->attachments as $attachment) {
            $file_encoded = base64_encode(file_get_contents($attachment));
            $mail->addAttachment(
                $file_encoded,
                mime_content_type($attachment),
                pathinfo($attachment, PATHINFO_BASENAME),
                'attachment'
            );
        }
        $sendgrid = new SendGrid(getenv('SENDGRID_API_KEY'));
        
        try {
            $response = $sendgrid->send($mail);
            
            if ($response->statusCode() == 202) {
                return $this->success('Email sent successfully with SendGrid');
            } else {
                return $this->error('Error while sending email using SendGrid: ' . $response->body());
            }
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
