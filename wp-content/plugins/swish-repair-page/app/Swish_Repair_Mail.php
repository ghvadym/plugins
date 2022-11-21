<?php


class Swish_Repair_Mail
{
    static function send_mail(string $email = '', string $message = '', string $subject = '')
    {
        if (!$email || !$message) {
            return false;
        }

        $blogName = get_option('blogname');
        $subject = $subject ?: $blogName;

        $headers = [
            'From: ' . $blogName,
            'content-type: text/html',
        ];

        return wp_mail(
            $email,
            $subject,
            $message,
            $headers
        );
    }
}