<?php
namespace Clicalmani\Fundation\Messenger\Serializer;

use Clicalmani\Fundation\Messenger\Serializer\SerializerInterface;

class Serializer implements SerializerInterface
{
    public function isSerialized(string $data, ?bool $strict = false): bool
    { 
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
    
        $data = trim( $data );
    
        if ('N;' === $data) {
            return true;
        }
    
        if (strlen( $data ) < 4) {
            return false;
        }
    
        if (':' !== $data[1]) {
            return false;
        }
    
        if (FALSE !== $strict) {

            $lastc = substr($data, -1);

            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }

        } else {

            $semicolon = strpos( $data, ';' );
            $brace     = strpos( $data, '}' );
            
            // Either ; or } must exist.
            if (FALSE === $semicolon && FALSE === $brace) {
                return false;
            }

            // But neither must be in the first X characters.
            if (FALSE !== $semicolon && $semicolon < 3) {
                return false;
            }

            if (FALSE !== $brace && $brace < 4) {
                return false;
            }
        }
    
        $token = $data[0];
    
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (FALSE === strpos($data, '"') ) {
                    return false;
                }
                // Or else fall through.
            case 'a':
            case 'O':
                return !!preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return !!preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
    
        return false;
    }

    /**
     * Check if data is serialized then unserialize, in othercase return the data.
     * 
     * @param string $data
     * @return string
     */
    public function maybeUnserialize(string $data) : string
    {
        if (is_serialized($data)) { 
            return @unserialize(trim($data));
        }
    
        return $data;
    }
}
