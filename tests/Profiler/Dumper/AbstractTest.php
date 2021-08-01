<?php

namespace Bubble\Tests\Profiler\Dumper;

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

use PHPUnit\Framework\TestCase;
use Bubble\Profiler\Profile;

abstract class AbstractTest extends TestCase
{
    protected function getProfile()
    {
        $profile = new Profile('main');
        $subProfiles = [
            $this->getIndexProfile(
                [
                    $this->getEmbeddedBlockProfile(),
                    $this->getEmbeddedTemplateProfile(
                        [
                            $this->getIncludedTemplateProfile(),
                        ]
                    ),
                    $this->getMacroProfile(),
                    $this->getEmbeddedTemplateProfile(
                        [
                            $this->getIncludedTemplateProfile(),
                        ]
                    ),
                ]
            ),
        ];

        $p = new \ReflectionProperty($profile, 'profiles');
        $p->setAccessible(true);
        $p->setValue($profile, $subProfiles);

        return $profile;
    }

    private function getIndexProfile(array $subProfiles = [])
    {
        return $this->generateProfile('main', 1, 'template', 'index.bubble', $subProfiles);
    }

    private function getEmbeddedBlockProfile(array $subProfiles = [])
    {
        return $this->generateProfile('body', 0.0001, 'block', 'embedded.bubble', $subProfiles);
    }

    private function getEmbeddedTemplateProfile(array $subProfiles = [])
    {
        return $this->generateProfile('main', 0.0001, 'template', 'embedded.bubble', $subProfiles);
    }

    private function getIncludedTemplateProfile(array $subProfiles = [])
    {
        return $this->generateProfile('main', 0.0001, 'template', 'included.bubble', $subProfiles);
    }

    private function getMacroProfile(array $subProfiles = [])
    {
        return $this->generateProfile('foo', 0.0001, 'macro', 'index.bubble', $subProfiles);
    }

    /**
     * @param string $name
     * @param float  $duration
     * @param bool   $isTemplate
     * @param string $type
     * @param string $templateName
     *
     * @return Profile
     */
    private function generateProfile($name, $duration, $type, $templateName, array $subProfiles = [])
    {
        $profile = new Profile($templateName, $type, $name);

        $p = new \ReflectionProperty($profile, 'profiles');
        $p->setAccessible(true);
        $p->setValue($profile, $subProfiles);

        $starts = new \ReflectionProperty($profile, 'starts');
        $starts->setAccessible(true);
        $starts->setValue($profile, [
            'wt' => 0,
            'mu' => 0,
            'pmu' => 0,
        ]);
        $ends = new \ReflectionProperty($profile, 'ends');
        $ends->setAccessible(true);
        $ends->setValue($profile, [
            'wt' => $duration,
            'mu' => 0,
            'pmu' => 0,
        ]);

        return $profile;
    }
}
