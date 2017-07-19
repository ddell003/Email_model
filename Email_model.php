<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Email_model
 * @package Models
 */
class Email_model extends CI_Model{

    /**
     * @var string
     */
    protected $table = 'email_queue';
    //protected $table = 'email_queue';
    protected $archive_table = 'email_archive';
    /**
     * @var string
     */
    protected $separator, $content_type;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->separator = md5(time());
        $this->charset = 'utf-8';
        $this->load->library('email');

    }

    // Wrapper for the php mail function, does not send if any of the fields are empty
    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param string $headers
     * @param null $attachments
     * @param bool $apply_theme
     * @return bool
     */
    public function push($to, $subject, $message, $headers = 'From: Mailer Bot <no-reply@gmail.net>', $attachments = null, $apply_theme = true)
    {
        
        if(empty($to) OR empty($subject) OR empty($message))
        {
            return FALSE;
        }

        $data = $this->_construct_ci_email($to, $subject, $message, $headers, $attachments, $apply_theme);

        if(!is_null($attachments)) {
            $headers = $this->setCorrectMime($headers, 'MIME-Version: 1.0');
            $headers = $this->setCorrectContentType($headers, 'Content-Type: multipart/mixed; boundary="'.$this->separator.'"');

            if($apply_theme)
                $message = $this->load->view('templates/emails/default', ['message' => $message], true);

            $message = $this->add_attachments($message, $attachments);
        } else if($apply_theme) {
            $message = $this->load->view('templates/emails/default', ['message' => $message], true);
            $headers = $this->setCorrectMime($headers, 'MIME-Version: 1.0');
            $headers = $this->setCorrectContentType($headers, 'Content-type: text/html; charset="'.$this->charset.'"');
        }

        return $this->db->insert($this->table, array('to'=>$to, 'subject'=>$subject, 'message'=>$message, 'headers'=>$headers, 'data' => $data));
    }

    /**
     * @param $to
     * @param $subject
     * @param $message
     * @param $headers
     * @param $attachments
     * @param $apply_theme
     * @return string
     */
    private function _construct_ci_email($to, $subject, $message, $headers, $attachments, $apply_theme)
    {
        $from_set = false;


        if(!empty($headers))
        {
            
            $headers = imap_rfc822_parse_headers($headers);

            if(isset($headers->from))
            {
                $from_addr = $headers->from[0]->mailbox.'@'.$headers->from[0]->host;
                $from_name = isset($headers->from[0]->personal) ? $headers->from[0]->personal : $from_addr;
                $this->email->from($from_addr, $from_name);
                $from_set = true;
            }

            if(isset($headers->cc))
            {
                $this->email->cc($headers->ccaddress);
            }

            if(isset($headers->bcc))
            {
                $this->email->bcc($headers->bccaddress);
            }

            if(isset($headers->reply_to))
            {
                $replyto_addr = $headers->reply_to[0]->mailbox.'@'.$headers->reply_to[0]->host;
                $replyto_name = isset($headers->reply_to[0]->personal) ? $headers->reply_to[0]->personal : $replyto_addr;
                $this->email->reply_to($replyto_addr, $replyto_name);
            }
        }

        if($apply_theme)
        {				//emails/folder_name/file_name
            $message = $this->load->view('templates/emails/default', ['message' => $message], true);
        }

        if(!$from_set)
        {
            $this->email->from('no-reply@me.net', 'Mailer Bot');
        }
       

        if($this->config->item('dev_email') !== false)
        {
            $this->email->to($this->config->item('dev_email'));
        }
        else
        {
            $this->email->to($to);
        }

        $this->email->subject($subject);
        $this->email->message($message);
        $this->email->set_alt_message(strip_tags($message));

        if(!is_null($attachments))
        {
            if(is_string($attachments))
            {
                $this->email->attach($attachments, 'attachment', true);
            }
            else if(is_array($attachments))
            {
                foreach($attachments as $attachment)
                {
                    $this->email->attach($attachment, 'attachment', true);
                }
            }
        }

        $data = serialize($this->email);
        $this->email->clear(true);

        return $data;
    }

    // Same as above except that this function accepts an array of emails to send
    /**
     * @param $emails_array
     * @param bool $apply_theme
     * @return bool
     */
    public function push_batch($emails_array, $apply_theme = true)
    {
        if(! is_array($emails_array))
        {
            return FALSE;
        }

        $success = TRUE;

        foreach($emails_array as $email)
        {
            $success = $this->push($email['to'], $email['subject'], $email['message'], (isset($email['headers']) ? $email['headers'] : null), (isset($email['attachments']) ? $email['attachments'] : null), $apply_theme) && $success;
        }

        return $success;
    }

    // This function attemps to email a user (based on a user id from the user table) with their work email first, falling back to a user's home email if available
    /**
     * @param $user_id
     * @param $subject
     * @param $msg
     * @param null $headers
     * @param null $attachments
     * @param bool $apply_theme
     * @return bool
     */
    public function email_user($user_id, $subject, $msg, $headers = null, $attachments = null, $apply_theme = true)
    {
        $result = $this->db->where('user_id', $user_id)->get('user')->row_array();

        if(empty($result))
        {
            return FALSE;
        }

        if( ! empty($result['email']))
        {
            return $this->push($result['email'], $subject, $msg, $headers, $attachments, $apply_theme);
        }

        if( ! empty($result['home_email']))
        {
            return $this->push($result['home_email'], $subject, $msg, $headers, $attachments, $apply_theme);
        }

        return FALSE;
    }

    /**
     * @param $user_email
     * @param $subject
     * @param $msg
     * @param $headers
     * @return bool
     */
    public function email_user_direct($user_email, $subject, $msg, $headers)
    {
        return $this->push($user_email, $subject, $msg, $headers);
    }

    // Standard email debug function
    /**
     * @param $to
     * @param $subject
     * @param $msg
     * @param $headers
     */
    public static function dump_email($to, $subject, $msg, $headers)
    {
        echo '<pre style="border-top: 3px solid #444; border-bottom: 3px solid #444;">';
        echo 'TO: '.$to.'<br>';
        echo 'SUBJECT: '.$subject.'<br>';
        echo 'HEADERS: '.$headers.'<br>';
        echo 'MESSAGE: '.$msg.'<br>';
        echo '</pre>';
    }

    /**
     * @param $headers
     * @param $mime
     * @return string
     */
    private function setCorrectMime($headers, $mime) {
        $mime_regex = "/MIME-Version:(.*)/i";
        if(preg_match($mime_regex, $headers) !== false)
            $headers = preg_replace($mime_regex, '', $headers);
        return trim($headers).PHP_EOL.$mime;
    }

    /**
     * @param $headers
     * @param $content_type
     * @return string
     */
    private function setCorrectContentType($headers, $content_type) {
        $contenttype_regex = "/Content-Type:(.*)/i";
        if(preg_match($contenttype_regex, $headers) !== false)
            $headers = preg_replace($contenttype_regex, '', $headers);

        return trim($headers).PHP_EOL.$content_type;
    }

    /**
     * @param $message
     * @param $attachments
     * @return string
     */
    private function add_attachments($message, $attachments) {
        if(is_string($attachments))
            $attachments = array(0 => $attachments);

        $eol = PHP_EOL;

        // message
        $return = "--".$this->separator.$eol;
        $return .= "Content-Type: text/html; charset=\"".$this->charset."\"".$eol;
        $return .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
        $return .= $message;

        foreach($attachments as $att) {
            $filename = basename($att);
            $handle = fopen($att, "r");
            $content = fread($handle, filesize($att));
            fclose($handle);
            $attobj = chunk_split(base64_encode($content));

            // add attachment
            $return .= $eol."--".$this->separator.$eol;
            $return .= "Content-Type: application/octet-stream; name=\"".$filename."\"".$eol;
            $return .= "Content-Transfer-Encoding: base64".$eol;
            $return .= "Content-Disposition: attachment".$eol.$eol;
            $return .= $attobj.$eol;
            $return .= "--".$this->separator;
        }
        $return .= "--";

        return $return;
    }

    /**
     * @param int $max
     * @return bool
     */
    public function process_queue($max = 30){
        $return = TRUE;
        $today = new DateTime();
        if(!file_exists('email.lock')) {

            file_put_contents('email.lock', $today->format('Y-m-d H:i:s'));
            $sent = array();
            $failed = array();
            $return = true;


            $emails = $this->db->limit($max)->order_by('failed ASC, created_at ASC')->get($this->table)->result_array();

            echo 'Sending '.count($emails).' email(s)...'.PHP_EOL;

            if($emails) {
                foreach($emails as $email){
                    
                    $em_obj = unserialize($email['data']);
                    if($email['data'] && $em_obj->send()){

                        $sent[] = $email['id'];
                        unset($email['id']);
                        $this->db->insert($this->archive_table, $email);

                    } else {
                        echo getenv(SMTP_AUTH).'<br>';
                        echo getenv(SMTP_HOST).'<br>';
                        echo getenv(SMTP_PASS).'<br>';
                        echo getenv(SMTP_PORT).'<br>';
                        die($em_obj->print_debugger());
                        $failed[] = array('id' => $email['id'], 'failed' => ($email['failed']+1));
                    }
                }

                if(!empty($sent)){
                    echo 'Sent '.count($emails).' email(s) successully...'.PHP_EOL;
                    $this->db->where_in('id',$sent)->delete($this->table);
                }
                if(count($emails) != count($sent)) {
                    echo (count($emails) - count($sent)) . ' email(s) failed.'.PHP_EOL;
                    $this->db->update_batch($this->table, $failed, 'id');
                    $return = false;
                }
            }
            unlink('email.lock');
            echo 'Email queue completed!'.PHP_EOL;
        } else {
            echo 'Email queue running...'.PHP_EOL;
        }
        return $return;
    }

    function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
    }
}
