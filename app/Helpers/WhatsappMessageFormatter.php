<?php

namespace App\Helpers;

class WhatsappMessageFormatter
{
    // WhatsApp API limits
    const BUTTON_TITLE_MAX_LENGTH = 20;
    const BUTTON_DESCRIPTION_MAX_LENGTH = 72;
    const BODY_TEXT_MAX_LENGTH = 1024;

    /**
     * Format a button title to meet WhatsApp's length requirements
     *
     * @param string $title
     * @return string
     */
    public static function formatButtonTitle(string $title): string
    {
        if (strlen($title) <= self::BUTTON_TITLE_MAX_LENGTH) {
            return $title;
        }

        // If the title is too long, truncate it and add ellipsis
        return substr($title, 0, self::BUTTON_TITLE_MAX_LENGTH - 3) . '...';
    }

    /**
     * Format a button description to meet WhatsApp's length requirements
     *
     * @param string $description
     * @return string
     */
    public static function formatButtonDescription(string $description): string
    {
        if (strlen($description) <= self::BUTTON_DESCRIPTION_MAX_LENGTH) {
            return $description;
        }

        // If the description is too long, truncate it and add ellipsis
        return substr($description, 0, self::BUTTON_DESCRIPTION_MAX_LENGTH - 3) . '...';
    }

    /**
     * Format body text to meet WhatsApp's length requirements
     *
     * @param string $text
     * @return string
     */
    public static function formatBodyText(string $text): string
    {
        if (strlen($text) <= self::BODY_TEXT_MAX_LENGTH) {
            return $text;
        }

        // If the text is too long, truncate it and add ellipsis
        return substr($text, 0, self::BODY_TEXT_MAX_LENGTH - 3) . '...';
    }

    /**
     * Format an interactive message to ensure all components meet WhatsApp's requirements
     *
     * @param array $message
     * @return array
     */
    public static function formatInteractiveMessage(array $message): array
    {
        if (isset($message['interactive']['body']['text'])) {
            $message['interactive']['body']['text'] = self::formatBodyText($message['interactive']['body']['text']);
        }

        if (isset($message['interactive']['action']['buttons'])) {
            foreach ($message['interactive']['action']['buttons'] as &$button) {
                if (isset($button['reply']['title'])) {
                    $button['reply']['title'] = self::formatButtonTitle($button['reply']['title']);
                }
            }
        }

        return $message;
    }
}
