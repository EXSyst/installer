<?php

/*
 * This file is part of the Installer package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Installer\Symfony;

/**
 * Extracted from SensioGeneratorBundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 */
class KernelManipulator extends Manipulator
{
    protected $reflected;

    public function __construct(string $kernel)
    {
        $this->reflected = new \ReflectionClass($kernel);
    }

    /**
     * Adds a bundle at the end of the existing ones.
     *
     * @param string $bundle The bundle class name
     *
     * @return bool true if it worked, false otherwise
     *
     * @throws \RuntimeException If bundle is already defined
     */
    public function addBundle(string $bundle): bool
    {
        if (!$this->getFilename()) {
            return false;
        }

        $src = file($this->getFilename());
        $method = $this->reflected->getMethod('registerBundles');
        $lines = array_slice($src, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);

        // Don't add same bundle twice
        if (false !== strpos(implode('', $lines), $bundle)) {
            return false;
        }

        $this->setCode(token_get_all('<?php '.implode('', $lines)), $method->getStartLine());

        while ($token = $this->next()) {
            // $bundles
            if (T_VARIABLE !== $token[0] || '$bundles' !== $token[1]) {
                continue;
            }

            // =
            $this->next();

            // array start with traditional or short syntax
            $token = $this->next();
            if (T_ARRAY !== $token[0] && '[' !== $this->value($token)) {
                return false;
            }

            // add the bundle at the end of the array
            while ($token = $this->next()) {
                // look for ); or ];
                if (')' !== $this->value($token) && ']' !== $this->value($token)) {
                    continue;
                }

                if (';' !== $this->value($this->peek())) {
                    continue;
                }

                $this->next();

                $leadingContent = implode('', array_slice($src, 0, $this->line));

                // trim semicolon
                $leadingContent = rtrim(rtrim($leadingContent), ';');

                // We want to match ) & ]
                $closingSymbolRegex = '#(\)|])$#';

                // get closing symbol used
                preg_match($closingSymbolRegex, $leadingContent, $matches);
                $closingSymbol = $matches[0];

                // remove last close parentheses
                $leadingContent = rtrim(preg_replace($closingSymbolRegex, '', rtrim($leadingContent)));

                if (substr($leadingContent, -1) !== '(' && substr($leadingContent, -1) !== '[') {
                    // end of leading content is not open parentheses or bracket, then assume that array contains at least one element
                    $leadingContent = rtrim($leadingContent, ',').',';
                }

                $lines = array_merge(
                    [$leadingContent, "\n"],
                    [str_repeat(' ', 12), sprintf('new %s(),', $bundle), "\n"],
                    [str_repeat(' ', 8), $closingSymbol.';', "\n"],
                    array_slice($src, $this->line)
                );

                file_put_contents($this->getFilename(), implode('', $lines));

                return true;
            }
        }
    }

    public function hasBundle(string $bundle): bool
    {
        $src = file($this->getFilename());
        $method = $this->reflected->getMethod('registerBundles');
        $lines = array_slice($src, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);

        // Don't add same bundle twice
        return false !== strpos(implode('', $lines), $bundle);
    }

    private function getFilename()
    {
        return $this->reflected->getFileName();
    }
}
