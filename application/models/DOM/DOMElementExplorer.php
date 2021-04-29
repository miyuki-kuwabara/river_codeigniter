<?php
namespace DOM {
    class DOMElementExplorer
    {
        public static function find_ancestor(\DOMNode $node, $tagName)
        {
            if ($node === null || $node->parentNode === null) {
                return null;
            }
            $parent = $node->parentNode;
            
            if ($parent->nodeType == XML_ELEMENT_NODE && $parent->tagName == $tagName) {
                return $parent;
            }

            return self::find_ancestor($parent, $tagName);
        }
    }
}
