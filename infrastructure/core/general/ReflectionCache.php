<?php
namespace infrastructure\core\general;

class ReflectionCache
{
    private const CACHE_DIR = INFRA_PATCH . '/cache/reflection';

    public static function get(string $class): array
    {
        if (function_exists('apcu_fetch')) {
            $data = apcu_fetch('reflect:' . $class, $ok);
            if ($ok && self::isValid($data)) {
                return $data;
            }
        }

        $cacheFile = self::getCacheFile($class);
        if (is_file($cacheFile)) {
            $data = include $cacheFile;
            if (is_array($data) && self::isValid($data)) {
                if (function_exists('apcu_store')) {
                    apcu_store('reflect:' . $class, $data);
                }
                return $data;
            }
        }

        $meta = self::buildMetadata($class);
        self::persistAtomic($cacheFile, $meta);
        if (function_exists('apcu_store')) {
            apcu_store('reflect:' . $class, $meta);
        }
        return $meta;
    }

    private static function getCacheFile(string $class): string
    {
        $safe = str_replace('\\', '_', ltrim($class, '\\'));
        self::ensureCacheDir();
        return self::CACHE_DIR . '/' . $safe . '.php';
    }

    private static function ensureCacheDir(): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            @mkdir(self::CACHE_DIR, 0777, true);
        }
    }

    private static function isValid(?array $data): bool
    {
        if (!is_array($data)) return false;
        $file = $data['__file'] ?? null;
        $mtime = $data['__mtime'] ?? null;
        $phpVer = $data['__php'] ?? null;
        if (!$file || !$mtime || !$phpVer) return false;
        if (!is_file($file)) return false;
        if ((int)$phpVer !== PHP_VERSION_ID) return false;
        return (int)filemtime($file) === (int)$mtime;
    }

    private static function buildMetadata(string $class): array
    {
        $rc = new \ReflectionClass($class);
        $file = $rc->getFileName() ?: '';
        $mtime = $file && is_file($file) ? (int)filemtime($file) : 0;

        $result = [
            'properties' => [],
            'methods' => [],
            'class_attributes' => [],
            'constructor' => [
                'parameters' => []
            ],
            '__file' => $file,
            '__mtime' => $mtime,
            '__php' => PHP_VERSION_ID,
        ];

        foreach ($rc->getAttributes() as $attr) {
            $result['class_attributes'][] = [
                'class' => $attr->getName(),
                'args' => $attr->getArguments(),
            ];
        }

        $ctor = $rc->getConstructor();
        if ($ctor) {
            $params = [];
            foreach ($ctor->getParameters() as $p) {
                $params[] = self::exportParam($p);
            }
            $result['constructor']['parameters'] = $params;
        }

        foreach ($rc->getProperties() as $prop) {
            if ($prop->getDeclaringClass()->getName() !== $class) {
                continue;
            }
            $attrs = [];
            foreach ($prop->getAttributes() as $attr) {
                $attrs[] = ['class' => $attr->getName(), 'args' => $attr->getArguments()];
            }
            $result['properties'][$prop->getName()] = $attrs;
        }

        foreach ($rc->getMethods() as $method) {
            $mAttrs = [];
            foreach ($method->getAttributes() as $attr) {
                $mAttrs[] = ['class' => $attr->getName(), 'args' => $attr->getArguments()];
            }
            $m = [
                'attributes' => $mAttrs,
                'declaringClass' => $method->getDeclaringClass()->getName(),
                'parameters' => [],
            ];
            foreach ($method->getParameters() as $p) {
                $m['parameters'][] = self::exportParam($p);
            }
            $result['methods'][$method->getName()] = $m;
        }

        return $result;
    }

    private static function exportParam(\ReflectionParameter $p): array
    {
        $type = $p->getType();
        $typeName = null;
        $isBuiltin = null;
        if ($type instanceof \ReflectionNamedType) {
            $typeName = $type->getName();
            $isBuiltin = $type->isBuiltin();
        }
        $hasDefault = $p->isDefaultValueAvailable();
        $default = null;
        if ($hasDefault) {
            try {
                $default = $p->getDefaultValue();
            } catch (\Throwable $e) {
                $default = null;
            }
        }
        return [
            'name' => $p->getName(),
            'hasType' => $type !== null,
            'type' => $typeName,
            'isBuiltin' => $isBuiltin,
            'isOptional' => $p->isOptional(),
            'allowsNull' => $type ? $type->allowsNull() : true,
            'hasDefault' => $hasDefault,
            'default' => $default,
        ];
    }

    private static function persistAtomic(string $file, array $meta): void
    {
        self::ensureCacheDir();
        $export = var_export($meta, true);
        $php = "<?php\nreturn " . $export . ";\n";
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $fp = @fopen($tmp, 'wb');
        if ($fp === false) return;
        flock($fp, LOCK_EX);
        fwrite($fp, $php);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        @rename($tmp, $file);
    }
}
