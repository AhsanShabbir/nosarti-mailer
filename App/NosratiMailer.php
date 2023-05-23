<?php

namespace App;

use App\Traits\HasResponses;
use App\Traits\Validations;
use Mailgun\Mailgun;
use PHPMailer\PHPMailer\PHPMailer;
use SendGrid;

class NosratiMailer
{

    use HasResponses;
    use Validations;
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
    private $checkValidation;


    public function __construct($driver = 'phpmailer', $validate = true)
    {
        $this->driver = $driver;
        $this->fromAddress = getenv('MAIL_FROM_ADDRESS');
        $this->fromName = getenv('MAIL_FROM_NAME');
        $this->checkValidation = $validate;
    }



    /**
     * Set the from address and name
     *
     * @param [type] $address
     * @param [type] $name
     * @return void
     */
    public function from($address, $name)
    {
        $this->fromAddress = $address;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Set To / Recipient Address
     *
     * @param array $to
     * @return void
     */
    public function to(array $to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Set CC
     *
     * @param [type] $cc
     * @return void
     */
    public function cc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * Set BCC
     *
     * @param [type] $bcc
     * @return void
     */
    public function bcc($bcc)
    {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * Set Subject Line
     *
     * @param [type] $subject
     * @return void
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set Body
     *
     * @param [type] $body
     * @param boolean $isHTML
     * @return void
     */
    public function body($body, $isHTML = true)
    {
        $this->body = $body;
        $this->isHTML = $isHTML;
        return $this;
    }

    /**
     * Set Alt Body for non HTML emails
     */
    public function altBody($altBody)
    {
        $this->altBody = $altBody;
        return $this;
    }



    /**
     * Attachment Files
     *
     * @param [type] $file
     * @return void
     */
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

    /**
     * Set Driver i.e PHPMailer, SendGrid, MailGun
     *
     * @param [type] $driver
     * @return void
     */
    public function setDriver($driver)
    {
        $this->driver = $driver;
        return $this;
    }



    public function send(): array
    {
        try {
            // Validate data
            if ($this->checkValidation) {
                $this->validate();
            }
            return match ($this->driver) {
                'phpmailer' =>  $this->sendWithPHPMailer(),
                'sendgrid' => $this->sendWithSendGrid(),
                'mailgun' => $this->sendWithMailGun(),
                default => $this->error('Invalid driver specified.'),
            };
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
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
            $mail->addAddress(...$recipient);
        }

        foreach ($this->cc as $email) {
            $mail->addCC(...$email);
        }

        foreach ($this->bcc as $email) {
            $mail->addBCC(...$email);
        }

        foreach ($this->attachments as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->Subject = $this->subject;
        $mail->Body = $this->body;
        $mail->AltBody = $this->altBody;
        $mail->isHTML($this->isHTML);

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
    private function sendWithMailGun()
    {

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


        $mg->messages()->send($domain, $data);

        return $this->success('Email sent successfully with MailGun');
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
        foreach ($this->to ?? [] as $email) {
            $mail->addTo(...$email);
        }
    
        foreach ($this->cc ?? [] as $email) {
            $mail->addCc(...$email);
        }
        foreach ($this->bcc ?? [] as $email) {
            $mail->addBcc(...$email);
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
