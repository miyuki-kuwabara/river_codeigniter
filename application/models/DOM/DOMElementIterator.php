<?php
namespace DOM {
    class DOMElementIterator implements \SeekableIterator
    {
        private $nodeList = null;
        private $index = -1;
        private $position = -1;
        private $predicate = null;

        public static function Create(\DOMNodeList $nodeList)
        {
            return new DOMElementIterator(
                $nodeList,
                function (\DOMNode $node) {
                    return $node->nodeType == XML_ELEMENT_NODE;
                });
        }

        public static function CreateWithTagSpec(\DOMNodeList $nodeList, $tagName)
        {
            return new DOMElementIterator(
                $nodeList,
                function (\DOMNode $node) use ($tagName) {
                    return $node->nodeType == XML_ELEMENT_NODE &&
                        $node->tagName == $tagName;
                });
        }

        private function __construct(\DOMNodeList $nodeList, $predicate)
        {
            $this->nodeList = $nodeList;
            $this->predicate = $predicate;
            $this->rewind();
        }

        public function seek($position)
        {
            $length = $this->nodeList->length;
            $index = $p = 0;
            do {
                $node = $this->nodeList->item($index);
                if ($this->isMatchCondition($node)) {
                    if ($position == $p) {
                        $this->index = $index;
                        $this->position = $p;
                        return;
                    }
                    $p++;
                }
                $index++;
            } while ($index < $length);

            throw new \OutOfBoundsException("invalid seek position ($position)");
        }

        public function current()
        {
            return $this->nodeList->item($this->index);
        }

        public function key()
        {
            return $this->position;
        }

        public function next()
        {
            $length = $this->nodeList->length;
            $this->index++;
            while ($this->index < $length) {
                if ($this->isMatchCondition($this->current())) {
                    break;
                }
                $this->index++;
            }
            $this->position++;
        }

        public function rewind()
        {
            $this->index = 0;
            $length = $this->nodeList->length;
            do {
                if ($this->isMatchCondition($this->current())) {
                    break;
                }
                $this->index++;
            } while ($this->index < $length);

            $this->position = 0;
        }

        public function valid()
        {
            return 0 <= $this->index
            && $this->index < $this->nodeList->length;
        }

        private function isMatchCondition(\DOMNode $node)
        {
            return call_user_func($this->predicate, $node);
        }
    }
}