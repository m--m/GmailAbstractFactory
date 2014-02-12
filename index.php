<?php
require_once "Mail.php";
include('mime.php');


/*
 * Send email from Gmail account with use Abstract Factory
 * Author: Michael Makarenkov
 */
interface EmailFactory {
    public function create();
}

class GmailFactory implements EmailFactory
{
    public function create()
    {
        return new Gmail();
    }
}

abstract class Email {
    
    protected $obj;
    protected $from;
    protected $subj;
    protected $to;
    
    abstract public function setSubject($subject);
    abstract public function setBody($body);
    abstract public function setFrom($from);
    abstract public function setTo($to);
    abstract public function send();
    
    public function setObj($obj)
    {
        $this->obj = $obj;
    }
    
    public function getObj()
    {
        return $this->obj;
    }
}

class Gmail extends Email
{ 
    public function __construct() {
        $this->setObj(new Mail_mime("rn"));
    }
    
    public function setBody($body)
    {
        $this->body = $body;
    }

    public function setFrom($from)
    {
        $this->from = $from;
    }
    
    public function setTo($to)
    {
        $this->to = $to;
    }
    
    public function setSubject($subj)
    {
        $this->subj = $subj;
    }
        
    public function send()
    {
        $headers = array(
            'From' =>  $this->from,
            'To' =>  $this->to,
            'Subject' => $this->subj
        );

        $headers = $this->getObj()->headers($headers);

        $smtp = Mail::factory('smtp', array(
                'host' => 'ssl://smtp.gmail.com',
                'port' => '465',
                'auth' => true,
                'username' => 'username',
                'password' => 'password'
            ));
        $body = $this->getObj()->get();
        $mail = $smtp->send($this->to, $headers, $this->body);
        
        if(PEAR::isError($mail)) {
            echo('<p>' . $mail->getMessage() . '</p>');
        } else {
            echo('<p>Message successfully sent!</p>');
        }
    }   
}

class Application {
    public function __construct(EmailFactory $ef) {
        $emailObj = $ef->create();
        $emailObj->setSubject("this is subject");
        $emailObj->setBody("this is body");
        $emailObj->setFrom("from@test.com");
        $emailObj->setTo("to@test.com");
        $emailObj->send();
    }
}

class ApplicationRunner {
    public static function run()
    {
        $gmail = new GmailFactory();
        new Application($gmail);
    }
}

ApplicationRunner::run();