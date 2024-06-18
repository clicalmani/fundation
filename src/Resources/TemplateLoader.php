<?php
namespace Clicalmani\Fundation\Resources;

use Clicalmani\Fundation\Exceptions\ResourceViewException;
use Twig\Loader\LoaderInterface;
use Twig\Source;

class TemplateLoader implements LoaderInterface
{
    protected $cache = [];
    protected $errorCache = [];

    private $rootPath;

    public function __construct()
    {
        $this->rootPath = resources_path('/views');
    }

    public function getSourceContext(string $name): Source
    {
        if (null === $path = $this->findTemplate($name)) {
            return new Source('', $name, '');
        }
        
        return new Source(file_get_contents($path), $name, $path);
    }

    public function getCacheKey(string $name): string
    {
        if (null === $path = $this->findTemplate($name)) {
            return '';
        }

        $len = \strlen($this->rootPath);

        if (0 === strncmp($this->rootPath, $path, $len)) {
            return substr($path, $len + 1);
        }

        return $path;
    }

    /**
     * @return bool
     */
    public function exists(string $name)
    {
        $name = $this->normalizeName($name);
        
        if (isset($this->cache[$name])) {
            return true;
        }

        return null !== $this->findTemplate($name, false);
    }

    public function isFresh(string $name, int $time): bool
    {
        // false support to be removed in 3.0
        if (null === $path = $this->findTemplate($name)) {
            return false;
        }

        return filemtime($path) < $time;
    }

    /**
     * @return string|null
     */
    protected function findTemplate(string $name, bool $throw = true)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) return $this->cache[$name];

        if (isset($this->errorCache[$name])) return null;

        $template_path = resources_path("/views/$name.template.php");

        if (file_exists($template_path)) {
            return $this->cache[$name] = $template_path;
        }

        $this->errorCache[$name] = sprintf('Unable to find template "%s" (looked into: %s).', $name, resources_path('/views'));

        if (!$throw) return null;

        throw new ResourceViewException($this->errorCache[$name]);
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('#/{2,}#', '/', str_replace('\\', '/', $name));
    }
}
