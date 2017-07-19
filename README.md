# Email_model
Email model for codeigniter

Benifits: 
Breaks emailing into two process and makes it easier to trouble shoot.
Takes advantage of CI email library

Need to set up the following tables:

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

Push to the model:

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

## Make cron job to push from queue

```
public function process_email() {

    return $this->email_model->process_queue(30);
}

```


