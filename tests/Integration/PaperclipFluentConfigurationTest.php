<?php
namespace Czim\Paperclip\Test\Integration;

use Czim\Paperclip\Attachment\Attachment;
use Czim\Paperclip\Config\Steps\AutoOrientStep;
use Czim\Paperclip\Config\Steps\ResizeStep;
use Czim\Paperclip\Config\Variant;
use Czim\Paperclip\Test\ProvisionedTestCase;
use SplFileInfo;

/**
 * Class PaperclipFluentConfigurationTest
 *
 * @uses \Czim\Paperclip\Config\Steps\AutoOrientStep
 * @uses \Czim\Paperclip\Config\Steps\ResizeStep
 */
class PaperclipFluentConfigurationTest extends ProvisionedTestCase
{

    /**
     * @test
     */
    function it_processes_and_stores_a_new_file_with_fluent_variant_steps_configuration()
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'variants' => [
                'test' => [
                    AutoOrientStep::make()->quiet(),
                    ResizeStep::make()->width(100)->height(100)
                        ->ignoreRatio()
                        ->convertOptions(['quality' => 90]),
                ],
            ],
        ]);

        $model->attachment = new SplFileInfo($this->getTestFilePath('rotated.jpg'));
        $model->save();

        $processedFilePath = $this->getUploadedAttachmentPath($model, 'rotated.jpg');

        static::assertInstanceOf(Attachment::class, $model->attachment);
        static::assertEquals('rotated.jpg', $model->attachment_file_name);
        static::assertFileExists($processedFilePath, "File was not stored");

        static::assertEquals(
            [
                "variants" => [
                    "test" => [
                        "resize"      => [
                            "dimensions"     => "100x100!",
                            "convertOptions" => [
                                "quality" => 90,
                            ],
                        ],
                        "auto-orient" => [
                            "quiet" => true,
                        ],
                    ],
                ],
            ],
            $model->attachment->getNormalizedConfig()
        );

        if (file_exists($processedFilePath)) {
            unlink($processedFilePath);
        }
    }

    /**
     * @test
     */
    function it_processes_and_stores_a_new_file_with_fluent_variant_configuration()
    {
        $model = $this->getTestModelWithAttachmentConfig([
            'variants' => [
                Variant::make('test')
                    ->steps([
                        AutoOrientStep::make()->quiet(),
                        ResizeStep::make()->width(100)->height(100)
                            ->ignoreRatio()
                            ->convertOptions(['quality' => 90]),
                    ])
                    ->extension('jpg'),
            ],
        ]);

        $model->attachment = new SplFileInfo($this->getTestFilePath('rotated.jpg'));
        $model->save();

        $processedFilePath = $this->getUploadedAttachmentPath($model, 'rotated.jpg');

        static::assertInstanceOf(Attachment::class, $model->attachment);
        static::assertEquals('rotated.jpg', $model->attachment_file_name);
        static::assertFileExists($processedFilePath, "File was not stored");

        static::assertEquals(
            [
                "variants" => [
                    "test" => [
                        "resize"      => [
                            "dimensions"     => "100x100!",
                            "convertOptions" => [
                                "quality" => 90,
                            ],
                        ],
                        "auto-orient" => [
                            "quiet" => true,
                        ],
                    ],
                ],
                'extensions' => [
                    'test' => 'jpg',
                ],
            ],
            $model->attachment->getNormalizedConfig()
        );

        if (file_exists($processedFilePath)) {
            unlink($processedFilePath);
        }
    }

}
