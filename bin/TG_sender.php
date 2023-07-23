<?php

class TG_sender{
    protected array $content;

    public function __construct(private string $url_api){}

    public function send_msg(int $chat_id, string $text, array $line_button=[], array $inline_button=[], bool $protect=true, bool $notification=true):object{
        $this->content = [];

        $this->content["chat_id"] = $chat_id;
        $this->content["text"] = base64_encode($text);
        $this->content["hash"] = hash("adler32", $text);
        $this->content["protect"] = $protect;
        $this->content["notification"] = $notification;

        if(count($line_button)>0) $this->content["line_button"] = $line_button;
        if(count($inline_button)>0) $this->content["inline_button"] = $inline_button;

        return $this->send_req();
    }
    protected function send_req():object{
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->content));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_URL, $this->url_api);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);


        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
         unset($this->content);


        $return = (object)[];
        $return->code = $info["http_code"];
        $return->scheme = $info["scheme"];
        $return->contentType = $info["content_type"];
        $return->ip = $info["primary_ip"];
        $return->port = $info["primary_port"];
        $return->body = $result;

        return $return;
    }
}