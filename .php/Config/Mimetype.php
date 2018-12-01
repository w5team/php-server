<?php
/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Config
 * @access      public
 * @since       0.3.0
 *
 * The MIT License
 *
 * Copyright 2015 http://google.com/+BillRocha.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


namespace Config;

class Mimetype
{
	// mime list
	private static $mimes = array(	
							'hqx'	=>	'application/mac-binhex40',
							'cpt'	=>	'application/mac-compactpro',
							'csv'	=>	array(
												'text/x-comma-separated-values', 
												'text/comma-separated-values', 
												'application/octet-stream', 
												'application/vnd.ms-excel', 
												'text/x-csv', 
												'text/csv', 
												'application/csv', 
												'application/excel', 
												'application/vnd.msexcel'
											),
							'bin'	=>	'application/macbinary',
							'dms'	=>	'application/octet-stream',
							'lha'	=>	'application/octet-stream',
							'lzh'	=>	'application/octet-stream',
							'exe'	=>	array('application/octet-stream', 'application/x-msdownload'),
							'class'	=>	'application/octet-stream',
							'psd'	=>	'application/x-photoshop',
							'so'	=>	'application/octet-stream',
							'sea'	=>	'application/octet-stream',
							'dll'	=>	'application/octet-stream',
							'oda'	=>	'application/oda',
							'pdf'	=>	array('application/pdf', 'application/x-download'),
							'ai'	=>	'application/postscript',
							'eps'	=>	'application/postscript',
							'ps'	=>	'application/postscript',
							'smi'	=>	'application/smil',
							'smil'	=>	'application/smil',
							'mif'	=>	'application/vnd.mif',
							'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
							'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
							'wbxml'	=>	'application/wbxml',
							'wmlc'	=>	'application/wmlc',
							'dcr'	=>	'application/x-director',
							'dir'	=>	'application/x-director',
							'dxr'	=>	'application/x-director',
							'dvi'	=>	'application/x-dvi',
							'gtar'	=>	'application/x-gtar',
							'gz'	=>	'application/x-gzip',
							'php'	=>	'application/x-httpd-php',
							'php4'	=>	'application/x-httpd-php',
							'php3'	=>	'application/x-httpd-php',
							'phtml'	=>	'application/x-httpd-php',
							'phps'	=>	'application/x-httpd-php-source',
							'js'	=>	'application/x-javascript',
							'swf'	=>	'application/x-shockwave-flash',
							'sit'	=>	'application/x-stuffit',
							'tar'	=>	'application/x-tar',
							'tgz'	=>	array('application/x-tar', 'application/x-gzip-compressed'),
							'xhtml'	=>	'application/xhtml+xml',
							'xht'	=>	'application/xhtml+xml',
							'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
							'mid'	=>	'audio/midi',
							'midi'	=>	'audio/midi',
							'mpga'	=>	'audio/mpeg',
							'mp2'	=>	'audio/mpeg',
							'mp3'	=>	array('audio/mpeg', 'audio/mpg', 'audio/mpeg3'),
							'aif'	=>	'audio/x-aiff',
							'aiff'	=>	'audio/x-aiff',
							'aifc'	=>	'audio/x-aiff',
							'ram'	=>	'audio/x-pn-realaudio',
							'rm'	=>	'audio/x-pn-realaudio',
							'rpm'	=>	'audio/x-pn-realaudio-plugin',
							'ra'	=>	'audio/x-realaudio',
							'rv'	=>	'video/vnd.rn-realvideo',
							'wav'	=>	'audio/x-wav',
							'bmp'	=>	'image/bmp',
							'gif'	=>	'image/gif',
							'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
							'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
							'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
							'png'	=>	array('image/png',  'image/x-png'),
							'tiff'	=>	'image/tiff',
							'tif'	=>	'image/tiff',
							'css'	=>	'text/css',
							'html'	=>	'text/html',
							'htm'	=>	'text/html',
							'shtml'	=>	'text/html',
							'txt'	=>	'text/plain',
							'text'	=>	'text/plain',
							'log'	=>	array('text/plain', 'text/x-log'),
							'rtx'	=>	'text/richtext',
							'rtf'	=>	'text/rtf',
							'xml'	=>	'text/xml',
							'xsl'	=>	'text/xml',
							'mpeg'	=>	'video/mpeg',
							'mpg'	=>	'video/mpeg',
							'mpe'	=>	'video/mpeg',
							'qt'	=>	'video/quicktime',
							'mov'	=>	'video/quicktime',
							'avi'	=>	'video/x-msvideo',
							'movie'	=>	'video/x-sgi-movie',
							'doc'	=>	'application/msword',
							'docx'	=>	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							'xlsx'	=>	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
							'word'	=>	array('application/msword', 'application/octet-stream'),
							'xl'	=>	'application/excel',
							'eml'	=>	'message/rfc822'
						);


	static function getByExt($ext)
	{
		if(array_key_exists($ext, self::$mimes)) {
			return is_array(self::$mimes) ? self::$mimes[$ext][0] : self::$mimes[$ext];
		}
		return false;
	}


}