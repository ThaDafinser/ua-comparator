<?php
/**
 * Copyright (c) 2015, Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
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
 * @category  UaComparator
 * @package   UaComparator
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 * @link      https://github.com/mimmi20/ua-comparator
 */

namespace UaComparator\Input;

use Crossjoin\Browscap\Browscap as CrBrowscap;
use Crossjoin\Browscap\Updater\Local;

/**
 * Browscap.ini parsing class with caching and update capabilities
 *
 * @category  UaComparator
 * @package   UaComparator
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class CrossJoin extends AbstractBrowscapInput
{
    /**
     * the parser class
     *
     * @var Browscap
     */
    private $parser = null;

    /**
     * sets the UA Parser detector
     *
     * @param \Crossjoin\Browscap\Browscap $parser
     *
     * @return CrossJoin
     */
    public function setParser(CrBrowscap $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * sets the main parameters to the parser
     *
     * @throws \UnexpectedValueException
     * @return Browscap
     */
    protected function initParser()
    {
        if (!($this->parser instanceof CrBrowscap)) {
            throw new \UnexpectedValueException(
                'the parser object has to be an instance of \Crossjoin\Browscap\Browscap'
            );
        }

        if (null !== $this->localFile) {
            $updater = new Local();
            $updater->setOption('LocalFile', $this->localFile);
            Browscap::setUpdater($updater);
        }

        return $this->parser;
    }

    /**
     * Gets the information about the browser by User Agent
     *
     * @return \BrowserDetector\Detector\Result the object containing the browsers details.
     * @throws \UnexpectedValueException
     */
    public function getBrowser()
    {
        $parserResult = (object) $this->initParser()->getBrowser($this->_agent)->getData();

        return $this->setResultData($parserResult);
    }
}
