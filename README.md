# Email_model
Email model for codeigniter

Benifits: 
Breaks emailing into two process and makes it easier to trouble shoot.
Takes advantage of CI email library

### CI Email config file
in config look to see if you have a file called email.php

if not, create the file and add:

```
<?php
    //use this if you do not have .env set up
   /* $config['protocol'] = 'smtp';
    $config['smtp_host'] = 'mailtrap.io'; //change this
    $config['smtp_port'] = '';
    $config['smtp_user'] = ''; //change this
    $config['smtp_pass'] = ''; //change this
    $config['mailtype'] = 'html';
    $config['charset'] = 'iso-8859-1';
    $config['wordwrap'] = TRUE;
    $config['newline'] = "\r\n"; //use double quotes to comply with RFC 822 standard
    $config['crlf'] = "\r\n";*/
    
    //connect to .env variables
    $config['protocol'] = 'smtp';
    $config['smtp_host'] = getenv('SMTP_HOST');
    $config['smtp_port'] = getenv('SMTP_PORT');
    $config['smtp_user'] = getenv('SMTP_USER'); //change this
    $config['smtp_pass'] = getenv('SMTP_PASS');
    $config['mailtype'] = 'html';
    $config['charset'] = 'iso-8859-1';
    $config['wordwrap'] = TRUE;
    $config['newline'] = "\r\n"; //use double quotes to comply with RFC 822 standard
    $config['crlf'] = "\r\n";
```

## Need to set up the following tables:

### Email Queue

The model will push your email to the queue
```
CREATE TABLE `email_queue` (
  `id` int(10) UNSIGNED NOT NULL,
  `to` text COLLATE utf8_unicode_ci NOT NULL,
  `bcc` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `message` blob NOT NULL,
  `headers` blob NOT NULL,
  `data` mediumblob NOT NULL,
  `failed` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
AND 

### Email Archive
proccess email will loop over emails in the queue and send them out and then store them here for future references
```
CREATE TABLE `email_archive` (
  `id` int(10) UNSIGNED NOT NULL,
  `to` text COLLATE utf8_unicode_ci NOT NULL,
  `bcc` text COLLATE utf8_unicode_ci NOT NULL,
  `subject` text COLLATE utf8_unicode_ci NOT NULL,
  `message` blob NOT NULL,
  `headers` blob NOT NULL,
  `data` mediumblob NOT NULL,
  `failed` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
```
## Pushing To email Queue
In your controller, to utilize email model:
Push Email to the model:

```
$to = 'someEmail@gmail.com';
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: no-reply@me.net'."\r\n";

//email subject
$subject = count($expiring_contracts).' Expiring Contracts';
$message = $this->load->view('templates/emails/expiring_contracts', array('expiring_contracts' => $expiring_contracts), true);

$this->email_model->push($to, $subject, $message, $headers);
```

## Make cron job to proccess queue

```
public function process_email() {

    return $this->email_model->process_queue(30);
}

```


