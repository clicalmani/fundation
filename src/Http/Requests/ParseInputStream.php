<?php
namespace Clicalmani\Fundation\Http\Requests;

use Clicalmani\Fundation\Support\Facades\Log;

/**
 * stream - Handle raw input stream
 *
 * LICENSE: This source file is subject to version 3.01 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html. If you did not receive a copy of
 * the GPL License and are unable to obtain it through the web, please
 *
 * @author jason.gerfen@gmail.com
 * @license http://www.gnu.org/licenses/gpl.html GPL License 3
 *
 * Massive modifications by TGE (dev@mycloudfulfillment.com) to support
 * proper parameter name processing and Laravel compatible UploadedFile
 * support. Class name changed to be more descriptive and less likely to
 * collide.
 *
 * Original Gist at:
 *   https://gist.github.com/jas-/5c3fdc26fedd11cb9fb5#file-class-stream-php
 *
 */
class ParseInputStream
{
    private $input;

    public function __construct(?array &$data = [])
    {
        $context = stream_context_create([
            'http' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'header' => "Content-Type: application/octet-stream\r\n"
            ]
        ]);

        $this->input = file_get_contents("php://input");
        
        parse_str(urldecode($this->input), $stream);
        
        if ($stream_boundary = $this->getStreamBoundary()) 
            $data = $this->parse(
                        tap(
                            preg_split("/-+$stream_boundary/", $this->input, -1, PREG_SPLIT_NO_EMPTY), 
                            fn(array &$parts) => array_pop($parts)
                        )
                    );
        else 
            $data = [
                'parameters' => $stream,
                'files' => []
            ];
    }

    /**
     * Get stream boundary
     * 
     * @return mixed Stream boundary if success, null if failure.
     */
    private function getStreamBoundary() : mixed
    {
        preg_match('/boundary=(.*)$/', @ $_SERVER['CONTENT_TYPE'], $matches);

        if ($boundry = @$matches[1]) return $boundry;

        return null;
    }

    /**
     * Parse the input
     * 
     * @param array $records
     * @return array
     */
    private function parse(array $records) : array
    {
        $results = [];

        foreach($records as $key => $record) {
            
            $block = $this->logic($record);
            
			foreach ($block['parameters'] as $key => $value) {
				$this->parseParameter($results, $key, $value);
			}

			foreach ($block['files'] as $key => $value) {
				$this->parseParameter($results, $key, $value);
			}
        }
        
		return $results;
    }

    /**
     * Seperate the input into parameters and files
     * 
     * @param string $block
     * @return array
     */
    private function logic(string $block)
    {
        if (strpos($block, 'application/octet-stream') !== FALSE) {
            return [
                'parameters' => $this->file($block),
                'files' => []
            ];
        }

        if (strpos($block, 'filename') !== FALSE) {
            return [
                'parameters' => [],
                'files' => $this->parseFile($block)
            ];
        }

        return [
            'parameters' => $this->parseParameters($block),
            'files' => []
        ];
    }

    /**
     * Get an octet stream
     * 
     * @param string $block
     * @return array Octet stream
     */
    private function file(string $block)
    {
        preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);

        return [
            $matches[1] => (@ $matches[2] !== NULL ? $matches[2] : '')
        ];
    }

    /**
     * Retrieve parameters
     * 
     * @param string $entry
     * @return array
     */
    private function parseParameters(string $entry) : array
    {
        $data = [];

        if ( preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $entry, $matches) ) {
	        if (preg_match('/^(.*)\[\]$/i', $matches[1], $tmp)) { 
	            $data[$tmp[1]][] = ($matches[2] !== NULL ? $matches[2] : '');
	        } else {
	            $data[$matches[1]] = (@ $matches[2] !== NULL ? $matches[2] : '');
	        }
		}

        return $data;
    }

    /**
     * Parse parameters
     * 
     * @param array &$params 
     * @param string $parameter
     * @param mixed $value
     */
    private function parseParameter(array &$params, string $parameter, mixed $value) 
    {
		if (strpos($parameter, '[') !== FALSE ) {  
			
			if ( preg_match('/^([^[]*)\[([^]]*)\](.*)$/', $parameter, $match) ) {

				$name = $match[1];
				$key  = $match[2];
				$rem  = $match[3];

				if ( $name !== '' && $name !== NULL ) {
					if ( ! isset($params[$name]) || ! is_array($params[$name]) ) {
						$params[$name] = [];
					}

					if ( strlen($rem) > 0 ) {
						if ( $key === '' || $key === NULL ) {
							$arr = [];
							$this->parseParameter( $arr, $rem, $value );
							$params[$name][] = $arr;
						} else {
							if ( !isset($params[$name][$key]) || !is_array($params[$name][$key]) ) {
								$params[$name][$key] = [];
							}
							$this->parseParameter( $params[$name][$key], $rem, $value );
						}
					} else {
						if ( $key === '' || $key === NULL ) {
							$params[$name][] = $value;
						} else {
							$params[$name][$key] = $value;
						}
					}
				} else {
					if ( strlen($rem) > 0 ) {
						if ( $key === '' || $key === NULL ) {
							$this->parseParameter( $params, $rem, $value );
						} else {
							if ( ! isset($params[$key]) || ! is_array($params[$key]) ) {
								$params[$key] = [];
							}

							$this->parseParameter( $params[$key], $rem, $value );
						}
					} else {
						if ( $key === '' || $key === NULL ) {
							$params[] = $value;
						} else {
							$params[$key] = $value;
						}
					}
				}
			} else {
				Log::warning("ParseInputStream Parameter name regex failed: '" . $parameter . "'");
			}
		} else {
            if (array_key_exists($parameter, $params) && is_array($params[$parameter])) $params[$parameter] = array_merge($params[$parameter], $value);
			else $params[$parameter] = $value;
		}
	}

    /**
     * Retrieve file
     * 
     * @param string $data File data
     * @return array
     */
    private function parseFile(string $data) : array
    {
        $result = [];
		$data = ltrim($data);

        if ($idx = strpos($data, "\r\n\r\n")) {
            $headers = substr( $data, 0, $idx );
			$content = substr( $data, $idx + 4, -2 ); // Skip the leading \r\n and strip the final \r\n

			$name = '-unknown-';
			$filename = '-unknown-';
			$filetype = 'application/octet-stream';

			$header = strtok( $headers, "\r\n" );
            while ($header !== FALSE) {
                if ( substr($header, 0, strlen("Content-Disposition: ")) == "Content-Disposition: " ) {
                    if ( preg_match('/name=\"([^\"]*)\"/', $header, $nmatch ) ) {
                        $name = $nmatch[1];
                    }
                    if ( preg_match('/filename=\"([^\"]*)\"/', $header, $nmatch ) ) {
                        $filename = $nmatch[1];
                    }
                } elseif ( substr($header, 0, strlen("Content-Type: ")) == "Content-Type: " ) {
                    $filetype = trim( substr($header, strlen("Content-Type: ")) );
                } else {
                    Log::notice( "PARSEINPUTSTREAM: Skipping Header: " . $header );
                }

                $header = strtok("\r\n");
            }

			if (substr($data, -2) === "\r\n") {
				$data = substr($data, 0, -2);
			}

            $ext = substr($filename, strrpos($filename, '.') + 1);
            $tmp_name = "php-" . substr( sha1(rand()), 0, 6 ) . ".$ext";
			$path = sys_get_temp_dir() . "/$tmp_name";

			$bytes = file_put_contents( $path, $content );

            tap(
                new UploadedFile($name), 
                fn(UploadedFile $upload) => $upload->addFile($filename, $path, $bytes, $filetype)
            );

        } else {
			Log::warning("ParseInputStream: Could not locate header separator in data:");
			Log::warning($data);
		}

        return $result;
    }
}
