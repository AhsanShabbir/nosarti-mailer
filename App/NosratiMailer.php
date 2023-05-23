<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;
use SendGrid;

class NosratiMailer
{

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
                $this->sendWithPHPMailer();
            }
            if($this->driver == 'sendgrid'){
                $this->sendWithSendGrid();
            }
        } catch (\Throwable $th) {
            echo $th;
        }
    }

    private function validate()
    {
        //check if subject, from, to, body are not empty
        if (empty($this->subject) || empty($this->fromAddress) || empty($this->fromName) || empty($this->to) || empty($this->body) || empty($this->altBody)) {
            throw new \Exception('Subject, From, To, Body, AltBody are required');
        }
    }

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

        foreach ($this->to as $email => $name) {
            $mail->addAddress($email, "Ahsan");
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
            var_dump($attachment);
            $mail->addAttachment($attachment);
        }

        $mail->Subject = $this->subject;
        $mail->Body = $this->body;
        $mail->AltBody = $this->altBody;
        if ($this->isHTML) {
            $mail->isHTML(true);
        }


        if (!$mail->send()) {
            echo 'Error while sending email using PHPMailer: ' . $mail->ErrorInfo;
            return false;
        }
        echo 'Mail sent successfully using PHPMailer';
        return true;
    }

    private function sendWithSendGrid()
    {
        $mail = new \SendGrid\Mail\Mail();
        $mail->setFrom($this->fromAddress, $this->fromName);
        $mail->setSubject($this->subject);
        
        foreach ($this->to as $email => $name) {
            $mail->addTo($email, $name);
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
        
        $mail->addContent("text/plain", $this->altBody);
        $mail->addContent("text/html", $this->body);
        
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
                echo 'Mail sent successfully using SendGrid';
                return true;
            } else {
                echo 'Error while sending email using SendGrid: ' . $response->body();
                return false;
            }
        } catch (\Throwable $th) {
            echo 'Error while sending email using SendGrid: ' . $th->getMessage();
            return false;
        }
    }
}
