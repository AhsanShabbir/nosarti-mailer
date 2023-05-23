<?php
namespace App\Traits;
trait Validations{
    private function validate()
    {
        //inlcude the validation file from session
        include_once __DIR__ .'/../../locale/' . $_SESSION['locale'] . '.php';

        //check if subject, from, to, body are not empty
        if (empty($this->subject) || empty($this->fromAddress) || empty($this->fromName) || empty($this->to) || empty($this->body) || empty($this->altBody)) {
            throw new \Exception($lang['empty_fields']);
        }

        if (!$this->validateEmails()) {
            throw new \Exception($lang['invalid_email']);
        }
    }

    /**
     * Validate Emails
     *
     * @return void
     */
    private function validateEmails(){
        {
            // Validate 'to' email addresses
            foreach ($this->to as $email) {
                if (!filter_var($email[0], FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
            }
           
    
            // Validate 'cc' email addresses
            foreach ($this->cc as $email) {
                if (!filter_var($email[0], FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
            }

        
            // Validate 'bcc' email addresses
            foreach ($this->bcc as $email) {
                if (!filter_var($email[0], FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
            }
        
            // Validate 'from' email address
            if (!filter_var($this->fromAddress, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        
            return true;
        }
    }

    /**
     * Undocumented function
     *
     * @param array $email
     * @return void
     */
    private function mgformat(array $emails){
        $formatted = [];
        foreach($emails as $email){
          $formatted[] = $this->mgFormatSingle($email[1], $email[0]);
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

    
    /**MailGun Attachments */
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
}