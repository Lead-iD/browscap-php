<?php

namespace BrowscapPHPTest\Helper;

use BrowscapPHP\Helper\IniLoader;
use FileLoader\Exception as LoaderException;

/**
 * Browscap.ini parsing class with caching and update capabilities
 *
 * PHP version 5
 *
 * Copyright (c) 2006-2012 Jonathan Stoppani
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    Browscap
 * @author     Vítor Brandão <noisebleed@noiselabs.org>
 * @copyright  Copyright (c) 1998-2015 Browser Capabilities Project
 * @version    3.0
 * @license    http://www.opensource.org/licenses/MIT MIT License
 * @link       https://github.com/browscap/browscap-php/
 */
class IniLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BrowscapPHP\Helper\IniLoader
     */
    private $object = null;

    public function setUp()
    {
        $this->object = new IniLoader();
    }

    /**
     *
     */
    public function testGetLoader()
    {
        self::assertInstanceOf('\FileLoader\Loader', $this->object->getLoader());
    }

    /**
     *
     */
    public function testSetGetLoader()
    {
        /** @var \FileLoader\Loader $loader */
        $loader = $this->getMock('\FileLoader\Loader', array(), array(), '', false);

        self::assertSame($this->object, $this->object->setLoader($loader));
        self::assertSame($loader, $this->object->getLoader());

        self::assertSame($this->object, $this->object->setLocalFile('test'));
        self::assertSame($loader, $this->object->getLoader());
    }

    /**
     *
     */
    public function testSetGetLogger()
    {
        /** @var \Monolog\Logger $logger */
        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        self::assertSame($this->object, $this->object->setLogger($logger));
        self::assertSame($logger, $this->object->getLogger());
    }

    /**
     * @expectedException \BrowscapPHP\Helper\Exception
     * @expectedExceptionMessage the filename can not be empty
     */
    public function testSetMissingRemoteFilename()
    {
        self::assertSame($this->object, $this->object->setRemoteFilename());
    }

    /**
     *
     */
    public function testSetRemoteFilename()
    {
        self::assertSame($this->object, $this->object->setRemoteFilename('testFile'));
    }

    /**
     * @expectedException \BrowscapPHP\Helper\Exception
     * @expectedExceptionMessage the filename can not be empty
     */
    public function testSetMissingLocalFile()
    {
        self::assertSame($this->object, $this->object->setLocalFile());
    }

    /**
     *
     */
    public function testSetLocalFile()
    {
        self::assertSame($this->object, $this->object->setLocalFile('testFile'));
    }

    /**
     *
     */
    public function testGetRemoteIniUrl()
    {
        $this->object->setRemoteFilename(IniLoader::PHP_INI_LITE);
        self::assertSame('http://browscap.org/stream?q=Lite_PHP_BrowscapINI', $this->object->getRemoteIniUrl());

        $this->object->setRemoteFilename(IniLoader::PHP_INI);
        self::assertSame('http://browscap.org/stream?q=PHP_BrowscapINI', $this->object->getRemoteIniUrl());

        $this->object->setRemoteFilename(IniLoader::PHP_INI_FULL);
        self::assertSame('http://browscap.org/stream?q=Full_PHP_BrowscapINI', $this->object->getRemoteIniUrl());
    }

    /**
     *
     */
    public function testGetRemoteVerUrl()
    {
        self::assertSame('http://browscap.org/version', $this->object->getRemoteTimeUrl());
    }

    /**
     *
     */
    public function testGetTimeout()
    {
        self::assertSame(5, $this->object->getTimeout());
    }

    /**
     *
     */
    public function testSetOptions()
    {
        $options = array();

        self::assertSame($this->object, $this->object->setOptions($options));
    }

    /**
     *
     */
    public function testLoad()
    {
        $loader = $this->getMock(
            '\FileLoader\Loader',
            array('load', 'setRemoteDataUrl', 'setRemoteVerUrl', 'setTimeout', 'getMTime'),
            array(),
            '',
            false
        );
        $loader
            ->expects(self::once())
            ->method('load')
            ->will(self::returnValue(true))
        ;
        $loader
            ->expects(self::once())
            ->method('setRemoteDataUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::never())
            ->method('setRemoteVerUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setTimeout')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::never())
            ->method('getMTime')
            ->will(self::returnValue(true))
        ;

        $this->object->setLoader($loader);

        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        $this->object->setLogger($logger);

        self::assertTrue($this->object->load());
    }

    /**
     * @expectedException \BrowscapPHP\Helper\Exception
     * @expectedExceptionMessage could not load the data file
     */
    public function testLoadException()
    {
        $loader = $this->getMock(
            '\FileLoader\Loader',
            array('load', 'setRemoteDataUrl', 'setRemoteVerUrl', 'setTimeout', 'getMTime'),
            array(),
            '',
            false
        );
        $loader
            ->expects(self::once())
            ->method('load')
            ->will(self::throwException(new LoaderException()))
        ;
        $loader
            ->expects(self::once())
            ->method('setRemoteDataUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::never())
            ->method('setRemoteVerUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setTimeout')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::never())
            ->method('getMTime')
            ->will(self::throwException(new LoaderException()))
        ;

        $this->object->setLoader($loader);

        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        $this->object->setLogger($logger);

        $this->object->load();
    }

    /**
     *
     */
    public function testGetMTime()
    {
        $loader = $this->getMock(
            '\FileLoader\Loader',
            array('load', 'setRemoteDataUrl', 'setRemoteVerUrl', 'setTimeout', 'getMTime'),
            array(),
            '',
            false
        );
        $loader
            ->expects(self::never())
            ->method('load')
            ->will(self::returnValue('Mon, 01 Jun 2015 08:53:57 +0000'))
        ;
        $loader
            ->expects(self::never())
            ->method('setRemoteDataUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setRemoteVerUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setTimeout')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('getMTime')
            ->will(self::returnValue('Mon, 01 Jun 2015 08:53:57 +0000'))
        ;

        $this->object->setLoader($loader);

        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        $this->object->setLogger($logger);

        self::assertSame(1433148837, $this->object->getMTime());
    }

    /**
     * @expectedException \BrowscapPHP\Helper\Exception
     * @expectedExceptionMessage could not load the new remote time
     */
    public function testGetMTimeException()
    {
        $loader = $this->getMock(
            '\FileLoader\Loader',
            array('load', 'setRemoteDataUrl', 'setRemoteVerUrl', 'setTimeout', 'getMTime'),
            array(),
            '',
            false
        );
        $loader
            ->expects(self::never())
            ->method('load')
            ->will(self::throwException(new LoaderException()))
        ;
        $loader
            ->expects(self::never())
            ->method('setRemoteDataUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setRemoteVerUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setTimeout')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('getMTime')
            ->will(self::throwException(new LoaderException()))
        ;

        $this->object->setLoader($loader);

        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        $this->object->setLogger($logger);

        $this->object->getMTime();
    }

    /**
     *
     */
    public function testGetRemoteVersion()
    {
        $loader = $this->getMock(
            '\FileLoader\Loader',
            array('load', 'setRemoteDataUrl', 'setRemoteVerUrl', 'setTimeout', 'getMTime'),
            array(),
            '',
            false
        );
        $loader
            ->expects(self::never())
            ->method('load')
            ->will(self::returnValue('6003'))
        ;
        $loader
            ->expects(self::never())
            ->method('setRemoteDataUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setRemoteVerUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setTimeout')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('getMTime')
            ->will(self::returnValue('6003'))
        ;

        $this->object->setLoader($loader);

        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        $this->object->setLogger($logger);

        self::assertSame(6003, $this->object->getRemoteVersion());
    }

    /**
     * @expectedException \BrowscapPHP\Helper\Exception
     * @expectedExceptionMessage could not load the new version
     */
    public function testGetRemoteVersionException()
    {
        $loader = $this->getMock(
            '\FileLoader\Loader',
            array('load', 'setRemoteDataUrl', 'setRemoteVerUrl', 'setTimeout', 'getMTime'),
            array(),
            '',
            false
        );
        $loader
            ->expects(self::never())
            ->method('load')
            ->will(self::throwException(new LoaderException()))
        ;
        $loader
            ->expects(self::never())
            ->method('setRemoteDataUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setRemoteVerUrl')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('setTimeout')
            ->will(self::returnSelf())
        ;
        $loader
            ->expects(self::once())
            ->method('getMTime')
            ->will(self::throwException(new LoaderException()))
        ;

        $this->object->setLoader($loader);

        $logger = $this->getMock('\Monolog\Logger', array(), array(), '', false);

        $this->object->setLogger($logger);

        $this->object->getRemoteVersion();
    }
}
