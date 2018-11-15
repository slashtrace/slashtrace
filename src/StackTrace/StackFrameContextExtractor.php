<?php

namespace SlashTrace\StackTrace;

use InvalidArgumentException;
use RuntimeException;
use SplFileObject;

class StackFrameContextExtractor
{

    /**
     * @param string $file
     * @param int $line
     * @param int $contextLines
     * @return array
     */
    public function getContext($file, $line, $contextLines = 15)
    {
        if ($file === "Unknown") {
            return [];
        }
        if (!file_exists($file)) {
            throw new RuntimeException();
        }
        if ($line < 1 || $contextLines < 0) {
            throw new InvalidArgumentException();
        }
        return $this->readContext($file, $line, $contextLines);
    }

    /**
     * @param string $file
     * @param int $line
     * @param int $contextLines
     * @return array
     */
    private function readContext($file, $line, $contextLines)
    {
        $lineIndex = $line - 1;

        $file = new SplFileObject($file);
        $file->seek(max(0, $lineIndex - $contextLines));

        $context = [];
        while (!$file->eof()) {
            $currentLine = $file->key();
            $context[$currentLine + 1] = rtrim($file->current(), "\r\n");

            if ($currentLine >= $lineIndex + $contextLines) {
                break;
            }
            $file->next();
        }

        return $context;
    }

}