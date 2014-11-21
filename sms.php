<?php
    // ****SMS-GATEWAY config**** // set up with values provided by your sms-gate //
    $smsGateId       = ;   // sms-gateway userid. 
    $smsGateKey      = ""; // sms-gateway security key. 
    $smsGateFrom     = ""; // displayed sms sender
    $smsGatePhone    = ""; // recipient's phone

    // **** Mailbox Config **** //
    $mailHost       = "{localhost:143/novalidate-cert}"; // your IMAP email server. localhost in our case.
    $mailLogin      = "___";                             // your email login
    $mailPass       = "___";                             // your email password

    function SMSSend($to, $text)
    {
    global $smsGateId;
    global $smsGateKey;
    global $smsGateFrom;

    $result = @file_get_contents('http://bytehand.com:3800/send?id='.$smsGateId.'&key='.$smsGateKey.'&to='.urlencode($to).'&from='.urlencode($smsGateFrom.'&text='.urlencode($text));
    if ($result === false)
        return false;
    else
        return true;
    }

    function MailDecode($enc){
        $parts = imap_mime_header_decode($enc);
        $str='';
        for ($p=0; $p<count($parts); $p++) {
            $ch=$parts[$p]->charset;
            $part=$parts[$p]->text;
            if ($ch!=='default') $str.=mb_convert_encoding($part,'UTF-8',$ch);
                            else $str.=$part;
        }
        return $str;
    }

    function MailGetRecent($host, $login, $passwd){
        global $smsGatePhone;
        $mbox = imap_open($host, $login, $passwd);
        $arr = imap_search($mbox, 'RECENT');

        if (!$arr) {
            return "ERR: NO CONNECTION TO MBOX";
        } else {
                foreach ( $arr as $el ) {
                    $headerArr = imap_headerinfo($mbox, $el);
                    $mailArr = array(
                        'sender'  => $headerArr->sender[0]->mailbox . "@" . $headerArr->sender[0]->host,
                        'name'    => MailDecode($headerArr->sender[0]->personal),
                        'subject' => MailDecode($headerArr->subject),
                    );
                $mailArr['subject'] = ($mailArr['subject'] == "" ? "<empty>" : $mailArr['subject']);
                $smsText = " " . $mailArr['name'] . " [" . $mailArr['sender'] . "] " . $mailArr['subject'];
                SMSSend( "$smsGatePhone", "$smsText" );
                }
            }
        }
    MailGetRecent( "$mailHost", "$mailLogin", "$mailPass" );
?>
