<?php

namespace SS6\ShopBundle\Tests\Twig;

use Intervention\Image\Image;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\FileUpload\FileUpload;
use SS6\ShopBundle\Model\Image\Processing\ImageEditor;
use SS6\ShopBundle\Twig\FileThumbnail\FileThumbnailExtension;
use SS6\ShopBundle\Twig\FileThumbnail\FileThumbnailInfo;

class FileThumbnailExtensionTest extends PHPUnit_Framework_TestCase {

	public function testGetFileThumbnailInfoByTemporaryFilenameBrokenImage() {
		$temporaryFilename = 'filename.jpg';

		$fileUploadMock = $this->getMock(FileUpload::class, ['getTemporaryFilepath'], [], '', false);
		$fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn('dir/' . $temporaryFilename);

		$exception = new \SS6\ShopBundle\Model\Image\Processing\Exception\FileIsNotSupportedImageException($temporaryFilename);
		$imageEditorMock = $this->getMock(ImageEditor::class, ['getImageThumbnail'], [], '', false);
		$imageEditorMock->expects($this->once())->method('getImageThumbnail')->willThrowException($exception);

		$fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageEditorMock);
		$fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

		$this->assertEquals(FileThumbnailExtension::DEFAULT_ICON_TYPE, $fileThumbnailInfo->getIconType());
		$this->assertNull($fileThumbnailInfo->getImageUri());
	}

	public function testGetFileThumbnailInfoByTemporaryFilenameImage() {
		$temporaryFilename = 'filename.jpg';
		$encodedData = 'encodedData';

		$fileUploadMock = $this->getMock(FileUpload::class, ['getTemporaryFilepath'], [], '', false);
		$fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn('dir/' . $temporaryFilename);

		$imageMock = $this->getMock(Image::class, ['encode']);
		$imageMock->expects($this->once())->method('encode')->willReturnSelf();
		$imageMock->setEncoded($encodedData);

		$imageEditorMock = $this->getMock(ImageEditor::class, ['getImageThumbnail'], [], '', false);
		$imageEditorMock->expects($this->once())->method('getImageThumbnail')->willReturn($imageMock);

		$fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageEditorMock);
		$fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

		$this->assertNull($fileThumbnailInfo->getIconType());
		$this->assertEquals($encodedData, $fileThumbnailInfo->getImageUri());
	}

	public function testGetFileThumbnailInfoByTemporaryFilenameImageDocument() {
		$temporaryFilename = 'filename.doc';

		$fileUploadMock = $this->getMock(FileUpload::class, ['getTemporaryFilepath'], [], '', false);
		$fileUploadMock->expects($this->any())->method('getTemporaryFilepath')->willReturn('dir/' . $temporaryFilename);

		$exception = new \SS6\ShopBundle\Model\Image\Processing\Exception\FileIsNotSupportedImageException($temporaryFilename);
		$imageEditorMock = $this->getMock(ImageEditor::class, ['getImageThumbnail'], [], '', false);
		$imageEditorMock->expects($this->once())->method('getImageThumbnail')->willThrowException($exception);

		$fileThumbnailExtension = new FileThumbnailExtension($fileUploadMock, $imageEditorMock);
		$fileThumbnailInfo = $fileThumbnailExtension->getFileThumbnailInfoByTemporaryFilename($temporaryFilename);

		$this->assertEquals('file-word-o', $fileThumbnailInfo->getIconType());
		$this->assertNull($fileThumbnailInfo->getImageUri());
	}
}
