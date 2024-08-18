<?php
namespace Clicalmani\Fundation\Http\Session;

/**
 * @see https://www.php.net/manual/en/class.sessionhandlerinterface.php
 */
class FileSessionHandler extends SessionHandler
{
    private string $savePath;

    public function open(string $path, string $name): bool
    {
        return $this->savePath = $path;
    }

    public function close(): bool
    {
        return true;
    }

    #[\ReturnTypeWillChange]
    public function read(string $id): string|false
    {
        return (string)$this->decrypt((string)@file_get_contents("$this->savePath/sess_$id"));
    }

    public function write(string $id, string $data): bool
    {
        return file_put_contents("$this->savePath/sess_$id", $this->encrypt($data)) === false ? false : true;
    }

    public function destroy($id): bool
    {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file)) {
            unlink($file);
        }

        return true;
    }

    #[\ReturnTypeWillChange]
    public function gc(int $max_lifetime): int|false
    {
        foreach (glob("$this->savePath/sess_*") as $file) {
            if (filemtime($file) + $max_lifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

    #[\ReturnTypeWillChange]
    public function create_sid()
    {
        return (string)session_create_id($this->getIdPrefix());
    }

    public function validate_sid(string $id)
    {
        $file = "$this->savePath/sess_$id";
        if (file_exists($file)) {
            return true;
        }

        return false;
    }
}
