<?php namespace CaffeineAddicts\MailAwesome;

use GuzzleHttp\Psr7\Response;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

/**
 * Class MailAwesome
 * @package CaffeineAddicts\MailAwesome
 *
 * @method static MailAwesome subject(string $subject) Sets the subject of the email
 * @method static MailAwesome to(string $subject) Sets the subject of the email
 * @method static MailAwesome from(string $subject) Sets the subject of the email
 * @method static MailAwesome cc(string $subject) Sets the subject of the email
 * @method static MailAwesome bcc(string $subject) Sets the subject of the email
 * @method static MailAwesome attach(string $file, string $filename = null, string $mimetype = null) Sets who this email is from
 *
 * @method MailAwesome subject(string $subject) Sets the subject of the email
 * @method MailAwesome to(mixed $email, string $name = null) Sets who this email is from
 * @method MailAwesome from(mixed $email, string $name = null) Sets who this email is from
 * @method MailAwesome cc(mixed $email, string $name = null) Sets who this email is from
 * @method MailAwesome bcc(mixed $email, string $name = null) Sets who this email is from
 * @method MailAwesome attach(string $file, string $filename = null, string $mimetype = null) Sets who this email is from
 *
 * @method MailAwesomeResponse send(string $template, array $values = []) Sets who this email is from
 * @method MailAwesomeResponse queue(string $template, array $values = [], int $queueTime = null) Sets who this email is from
 */
class MailAwesome {

    protected $to = [];

    protected $from = [];

    protected $cc = [];

    protected $bcc = [];

    protected $attachments = [];

    protected $subject = "";



    protected function callAttach($file, $filename = null, $mimetype = null)
    {
        if (!is_array($file))
        {
            array_push($this->attachments, [
                'file' => $file,
                'filename' => !empty($filename) ? $filename : null,
                'mimetype' => !empty($mimetype) ? $mimetype : null,
            ]);

            return $this;
        }

        return $this;
    }

    protected function callSend($template, array $values = [])
    {

        return new MailAwesomeResponse(
            $this,
            Mail::send($template, $values, function($message) {
                $this->sendClosure($message);
            })
        );

    }

    protected function callQueue($template, array $values = [], $queueTime = null)
    {
        if (empty($queueTime))
        {
            Mail::queue($template, $values, function ($message) {
                $this->sendClosure($message);
            });

            return $this;
        }

        Mail::later($queueTime, $template, $values, function ($message) {
            $this->sendClosure($message);
        });

        return $this;
    }


    protected function sendClosure(Message $mailObject)
    {
        $mailObject->subject($this->subject);

        $mailObject->from($this->from[0]['address'], $this->from[0]['name']);

        foreach ($this->to as $toDetails)
        {
            $mailObject->to($toDetails['address'], $toDetails['name']);
        }

        foreach ($this->cc as $ccDetails)
        {
            $mailObject->cc($ccDetails['address'], $ccDetails['name']);
        }

        foreach ($this->bcc as $bccDetails)
        {
            $mailObject->bcc($bccDetails['address'], $bccDetails['name']);
        }

        foreach ($this->attachments as $attachmentDetails)
        {
            $mailObject->attach($attachmentDetails['file'], ['as' => $attachmentDetails['filename'], 'mime' => $attachmentDetails['mimetype']]);
        }
    }


    protected function callSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    protected function callTo($email, $name = null)
    {
        return $this->setEmailDetails('to', $email, $name);
    }

    protected function callFrom($email, $name = null)
    {
        return $this->setEmailDetails('from', $email, $name);
    }

    protected function callCc($email, $name = null)
    {
        return $this->setEmailDetails('cc', $email, $name);
    }

    protected function callBcc($email, $name = null)
    {
        return $this->setEmailDetails('bcc', $email, $name);
    }

    protected function setEmailDetails($item, $email, $name = null)
    {
        $function = $this->setCallFunction($item);

        if (!is_array($email))
        {
            array_push($this->$item, [
                'address' => $email,
                'name' => !empty($name) ? $name : null,
            ]);

            return $this;
        }

        foreach ($email as $name => $address)
        {
            if (is_numeric($name))
            {
                $this->$function($address);
            } else {
                $this->$function($address, $name);
            }
        }

        return $this;
    }

    protected function setCallFunction($name)
    {
        return "call" . ucwords($name);
    }

    public function __call($methodName, $arguments)
    {
        $methodName = $this->setCallFunction($methodName);

        if (method_exists($this, $methodName))
        {
            return call_user_func_array([$this, $methodName], $arguments);
        }
    }

    public static function __callStatic($methodName, $arguments)
    {
        $newClass = new self();
        $methodName = $newClass->setCallFunction($methodName);

        if (method_exists($newClass, $methodName))
        {
            return call_user_func_array([$newClass, $methodName], $arguments);
        }
    }

}