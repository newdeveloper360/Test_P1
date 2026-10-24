<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

final class CleanHtml
{
    public static function make(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $body = $document->getElementsByTagName('body')->item(0);
        if (!$body) return '';
        self::clean($body);
        $result = '';
        foreach ($body->childNodes as $child) $result .= $document->saveHTML($child);
        return $result;
    }

    private static function clean(DOMNode $parent): void
    {
        $allowed = ['p', 'br', 'h2', 'h3', 'h4', 'strong', 'b', 'em', 'i', 'u', 's',
            'ul', 'ol', 'li', 'blockquote', 'a', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'hr'];
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMElement) {
                $tag = strtolower($node->tagName);
                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form', 'input'], true)) {
                    $parent->removeChild($node);
                    continue;
                }
                self::clean($node);
                if (!in_array($tag, $allowed, true)) {
                    while ($node->firstChild) $parent->insertBefore($node->firstChild, $node);
                    $parent->removeChild($node);
                    continue;
                }
                $href = $tag === 'a' ? $node->getAttribute('href') : '';
                foreach (iterator_to_array($node->attributes) as $attribute) $node->removeAttribute($attribute->name);
                if ($tag === 'a' && preg_match('~^(https?://|mailto:|/[^/]|#[\w-])~i', $href)) {
                    $node->setAttribute('href', $href);
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            } elseif ($node->nodeType !== XML_TEXT_NODE) {
                $parent->removeChild($node);
            }
        }
    }
}
