<?php

namespace ValueParsers\Test;

use DataValues\TimeValue;
use ValueParsers\CalendarModelParser;
use ValueParsers\IsoTimestampParser;
use ValueParsers\MonthNameUnlocalizer;
use ValueParsers\ParserOptions;
use ValueParsers\PhpDateTimeParser;
use ValueParsers\ValueParser;

/**
 * @covers ValueParsers\PhpDateTimeParser
 *
 * @group ValueParsers
 * @group WikibaseLib
 * @group Wikibase
 * @group TimeParsers
 *
 * @licence GNU GPL v2+
 * @author Adam Shorland
 * @author Thiemo Mättig
 */
class PhpDateTimeParserTest extends StringValueParserTest {

	/**
	 * @deprecated since 0.3, just use getInstance.
	 */
	protected function getParserClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return PhpDateTimeParser
	 */
	protected function getInstance() {
		$options = new ParserOptions();

		return new PhpDateTimeParser(
			new MonthNameUnlocalizer( array() ),
			$this->getEraParser(),
			new IsoTimestampParser( new CalendarModelParser( $options ), $options )
		);
	}

	/**
	 * @return ValueParser
	 */
	private function getEraParser() {
		$mock = $this->getMockBuilder( 'ValueParsers\ValueParser' )
			->disableOriginalConstructor()
			->getMock();

		$mock->expects( $this->any() )
			->method( 'parse' )
			->with( $this->isType( 'string' ) )
			->will( $this->returnCallback(
				function( $value ) {
					$sign = '+';
					// Tiny parser that supports a single negative sign only
					if ( $value[0] === '-' ) {
						$sign = '-';
						$value = substr( $value, 1 );
					}
					return array( $sign, $value ) ;
				}
			) );

		return $mock;
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argList = array();

		$valid = array(
			// Normal/easy dates
			'10/10/2010' =>
				array( '+0000000000002010-10-10T00:00:00Z' ),
			'10.10.2010' =>
				array( '+0000000000002010-10-10T00:00:00Z' ),
			'  10.  10.  2010  ' =>
				array( '+0000000000002010-10-10T00:00:00Z' ),
			'10,10,2010' =>
				array( '+0000000000002010-10-10T00:00:00Z' ),
			'10 10 2010' =>
				array( '+0000000000002010-10-10T00:00:00Z' ),
			'10/10/0010' =>
				array( '+0000000000000010-10-10T00:00:00Z' ),
			'1 July 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'1. July 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'1 July, 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'1 Jul. 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'1 Jul 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'January 9 1920' =>
				array( '+0000000000001920-01-09T00:00:00Z' ),
			'Feb 11 1930' =>
				array( '+0000000000001930-02-11T00:00:00Z' ),
			'1st July 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'2nd July 2013' =>
				array( '+0000000000002013-07-02T00:00:00Z' ),
			'3rd July 2013' =>
				array( '+0000000000002013-07-03T00:00:00Z' ),
			'1th July 2013' =>
				array( '+0000000000002013-07-01T00:00:00Z' ),
			'2th July 2013' =>
				array( '+0000000000002013-07-02T00:00:00Z' ),
			'3th July 2013' =>
				array( '+0000000000002013-07-03T00:00:00Z' ),
			'4th July 2013' =>
				array( '+0000000000002013-07-04T00:00:00Z' ),

			// Year first dates
			'2009-01-09' =>
				array( '+0000000000002009-01-09T00:00:00Z' ),
			'55-01-09' =>
				array( '+0000000000000055-01-09T00:00:00Z' ),
			'555-01-09' =>
				array( '+0000000000000555-01-09T00:00:00Z' ),
			'33300-1-1' =>
				array( '+0000000000033300-01-01T00:00:00Z' ),
			'3330002-1-1' =>
				array( '+0000000003330002-01-01T00:00:00Z' ),

			// Less than 4 digit years
			'10/10/10' =>
				array( '+0000000000000010-10-10T00:00:00Z' ),
			'9 Jan 09' =>
				array( '+0000000000000009-01-09T00:00:00Z' ),
			'1/1/1' =>
				array( '+0000000000000001-01-01T00:00:00Z' ),
			'1-1-1' =>
				array( '+0000000000000001-01-01T00:00:00Z' ),
			'31-1-55' =>
				array( '+0000000000000055-01-31T00:00:00Z' ),
			'10-10-100' =>
				array( '+0000000000000100-10-10T00:00:00Z' ),
			'4th July 11' =>
				array( '+0000000000000011-07-04T00:00:00Z' ),
			'4th July 111' =>
				array( '+0000000000000111-07-04T00:00:00Z' ),
			'4th July 1' =>
				array( '+0000000000000001-07-04T00:00:00Z' ),
			'12.Jun.10x' =>
				array( '+0000000000000010-06-12T00:00:00Z' ),

			// More than 4 digit years
			'4th July 10000' =>
				array( '+0000000000010000-07-04T00:00:00Z' ),
			'10/10/22000' =>
				array( '+0000000000022000-10-10T00:00:00Z' ),
			'1-1-33300' =>
				array( '+0000000000033300-01-01T00:00:00Z' ),
			'4th July 7214614279199781' =>
				array( '+7214614279199781-07-04T00:00:00Z' ),
			'-10100-02-29' =>
				array( '-0000000000010100-03-01T00:00:00Z' ),

			// Years with leading zeros
			'009-08-07' =>
				array( '+0000000000000009-08-07T00:00:00Z' ),
			'000001-07-04' =>
				array( '+0000000000000001-07-04T00:00:00Z' ),
			'0000001-07-04' =>
				array( '+0000000000000001-07-04T00:00:00Z' ),
			'00000001-07-04' =>
				array( '+0000000000000001-07-04T00:00:00Z' ),
			'000000001-07-04' =>
				array( '+0000000000000001-07-04T00:00:00Z' ),
			'00000000000-07-04' =>
				array( '+0000000000000000-07-04T00:00:00Z' ),
			'4th July 00000002015' =>
				array( '+0000000000002015-07-04T00:00:00Z' ),
			'00000002015-07-04' =>
				array( '+0000000000002015-07-04T00:00:00Z' ),
			'4th July 00000092015' =>
				array( '+0000000000092015-07-04T00:00:00Z' ),
			'00000092015-07-04' =>
				array( '+0000000000092015-07-04T00:00:00Z' ),

			// Hour, minute and second precision
			'4 July 2015 23:59' =>
				array( '+0000000000002015-07-04T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4 July 100 23:59' =>
				array( '+0000000000000100-07-04T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4 July 015 23:59' =>
				array( '+0000000000000015-07-04T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4 July 15 23:59' =>
				array( '+0000000000000015-07-04T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4.7.015 23:59' =>
				array( '+0000000000000015-07-04T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4.7.15 23:59' =>
				array( '+0000000000000015-07-04T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4/7/015 23:59' =>
				array( '+0000000000000015-04-07T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4/7/15 23:59' =>
				array( '+0000000000000015-04-07T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4th July 2015 12:00' =>
				array( '+0000000000002015-07-04T12:00:00Z', 0, 0, 0, TimeValue::PRECISION_HOUR ),
			'2015-07-04 12:00' =>
				array( '+0000000000002015-07-04T12:00:00Z', 0, 0, 0, TimeValue::PRECISION_HOUR ),
			'2015-07-04 12:30' =>
				array( '+0000000000002015-07-04T12:30:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'2015-07-04 12:30:29' =>
				array( '+0000000000002015-07-04T12:30:29Z', 0, 0, 0, TimeValue::PRECISION_SECOND ),
			'15.07.04 23:59' =>
				array( '+0000000000000004-07-15T23:59:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'15.07.04 00:01' =>
				array( '+0000000000000004-07-15T00:01:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'15-07-01 12:37:00' =>
				array( '+0000000000000001-07-15T12:37:00Z', 0, 0, 0, TimeValue::PRECISION_MINUTE ),
			'4th July 15 12:00' =>
				array( '+0000000000000015-07-04T12:00:00Z', 0, 0, 0, TimeValue::PRECISION_HOUR ),
			'July 4th 15 12:00' =>
				array( '+0000000000000015-07-04T12:00:00Z', 0, 0, 0, TimeValue::PRECISION_HOUR ),

			// Testing leap year stuff
			'10000-02-29' =>
				array( '+0000000000010000-02-29T00:00:00Z' ),
			'10100-02-29' =>
				array( '+0000000000010100-03-01T00:00:00Z' ),
			'10400-02-29' =>
				array( '+0000000000010400-02-29T00:00:00Z' ),
		);

		foreach ( $valid as $value => $args ) {
			$expected = new TimeValue(
				$args[0],
				array_key_exists( 1, $args ) ? $args[1] : 0,
				array_key_exists( 2, $args ) ? $args[2] : 0,
				array_key_exists( 3, $args ) ? $args[3] : 0,
				array_key_exists( 4, $args ) ? $args[4] : TimeValue::PRECISION_DAY,
				array_key_exists( 5, $args ) ? $args[5] : IsoTimestampParser::CALENDAR_GREGORIAN
			);
			$argList[] = array( (string)$value, $expected );
		}

		return $argList;
	}

	/**
	 * @see StringValueParserTest::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			// These are just wrong!
			'June June June',
			'111 111 111',
			'101st July 2015',
			'2015-07-101',
			'10  .10  .2010',
			'10...10...2010',
			'00-00-00',
			'99-00-00',
			'111-00-00',
			'2015-00-00',
			'00000000099-00-00',
			'00000002015-00-00',
			'92015-00-00',
			'Jann 2014',
			'1980x',
			'1980s', // supported by MWTimeIsoParser
			'1980',
			'1980ss',
			'1980er',
			'1980UTC', // we don't support year + timezone here
			'1980America/New_York',
			'1980 America/New_York',
			'1980+3',
			'1980+x',
			'x',
			'x x x',
			'zz',
			'America/New_York'
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}

}
