<?php namespace utils;
/**
 * Copyright 2015 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/

use Illuminate\Support\Facades\Log;

/**
 * Class FilterParser
 * @package utils
 */
final class FilterParser
{
    /**
     * @param $filters
     * @param $allowed_fields
     * @param string $main_operator
     * @return Filter
     * @throws FilterParserException
     */
    public static function parse($filters, $allowed_fields = [])
    {
        Log::debug
        (
            sprintf
            (
                "FilterParser::parse filters %s allowed_fields %s",
                json_encode($filters),
                json_encode($allowed_fields),
            )
        );

        $res = [];
        $matches = [];
        $ops = [];
        if (!is_array($filters))
            $filters = array($filters);

        foreach ($filters as $filter) // parse Main Filters ( 1st grade )
        {

            // check main operator
            $main_op_matches = null;
            if (preg_match('/(and|or)\((.*)\)/i', $filter, $main_op_matches)) {
                $ops[] = strtoupper($main_op_matches[1]);
                $filter = $main_op_matches[2];
            } else {
                $ops[] = Filter::MainOperatorAnd;
            }

            $f = null;
            // parse OR filters
            $or_filters = preg_split("|(?<!\\\),|", $filter);

            if (count($or_filters) > 1) {
                $f = [];
                foreach ($or_filters as $of) {

                    //single filter
                    if (empty($of)) continue;

                    list($field, $op, $value) = self::filterExpresion($of);

                    if (!isset($allowed_fields[$field])) {
                        throw new FilterParserException(sprintf("filter by field %s is not allowed", $field));
                    }
                    if (!in_array($op, $allowed_fields[$field])) {
                        throw new FilterParserException(sprintf("%s op is not allowed for filter by field %s", $op, $field));
                    }
                    // check if value has AND or OR values on same field
                    $same_field_op = null;
                    if (str_contains($value, '&&')) {
                        $values = explode('&&', $value);
                        if (count($values) > 1) {
                            $value = $values;
                            $same_field_op = 'AND';
                        }
                    } else if (str_contains($value, '||')) {
                        $values = explode('||', $value);
                        if (count($values) > 1) {
                            $value = $values;
                            $same_field_op = 'OR';
                        }
                    }

                    $f_or = self::buildFilter($field, $op, $value, $same_field_op);
                    if (!is_null($f_or))
                        $f[] = $f_or;
                }
            } else {
                //single filter

                list($field, $op, $value) = self::filterExpresion($filter);

                // check if value has AND or OR values on same field
                $same_field_op = null;
                if (str_contains($value, '&&')) {
                    $values = explode('&&', $value);
                    if (count($values) > 1) {
                        $value = $values;
                        $same_field_op = 'AND';
                    }
                } else if (str_contains($value, '||')) {
                    $values = explode('||', $value);
                    if (count($values) > 1) {
                        $value = $values;
                        $same_field_op = 'OR';
                    }
                }

                if (!isset($allowed_fields[$field])) {
                    throw new FilterParserException(sprintf("Filter by field %s is not allowed.", $field));
                }

                if (!is_array($allowed_fields[$field])) {
                    throw new FilterParserException(sprintf("Filter by field %s is not an array.", $field));
                }

                if (!in_array($op, $allowed_fields[$field])) {
                    throw new FilterParserException(sprintf("%s op is not allowed for filter by field %s.", $op, $field));
                }

                $f = self::buildFilter($field, $op, $value, $same_field_op);
            }

            if (!is_null($f))
                $res[] = $f;
        }

        $res = new Filter($res, $filters, $ops);
        Log::debug(sprintf("FilterParser::parse result %s", $res));
        return $res;
    }

    /**
     * @param string $exp
     * @return array
     * @throws FilterParserException
     */
    public static function filterExpresion(string $exp)
    {

        Log::debug(sprintf("FilterParser::filterExpresion %s", $exp));
        if (!preg_match('/\[\]|\(\)|>=|<=|<>|==|=\@|\@\@|<|>/i', $exp, $matches))
            throw new FilterParserException(sprintf("Invalid filter format %s (should be [:FIELD_NAME:OPERAND:VALUE]).", $exp));

        $op = $matches[0];
        $operands = explode($op, $exp, 2);
        $field = strtolower(trim($operands[0]));
        $value = str_replace(['\\,','\\;'], [',',';'], urldecode($operands[1]));
        Log::debug(sprintf("FilterParser::filterExpresion field %s op %s value %s", $field, $op, json_encode($value)));
        return [$field, $op, $value];
    }

    /**
     * Factory Method
     *
     * @param string $field
     * @param string $op
     * @param mixed $value
     * @param string $same_field_op
     * @return FilterElement|null
     */
    public static function buildFilter($field, $op, $value, $same_field_op = null)
    {
        Log::debug
        (
            sprintf
            (
                "FilterParser::buildFilter field %s op %s value %s same_field_op %s",
                $field,
                $op,
                json_encode($value),
                $same_field_op
            )
        );

        switch ($op) {
            case '==':
                return FilterElement::makeEqual($field, $value, $same_field_op);
                break;
            case '=@':
                return FilterElement::makeLike($field, $value, $same_field_op);
                break;
            case '@@':
                return FilterElement::makeLikeStart($field, $value, $same_field_op);
                break;
            case '>':
                return FilterElement::makeGreather($field, $value, $same_field_op);
                break;
            case '>=':
                return FilterElement::makeGreatherOrEqual($field, $value, $same_field_op);
                break;
            case '[]':
                return FilterElement::makeBetween($field, $value, $same_field_op);
                break;
            case '()':
                return FilterElement::makeBetweenStrict($field, $value, $same_field_op);
                break;
            case '<':
                return FilterElement::makeLower($field, $value, $same_field_op);
                break;
            case '<=':
                return FilterElement::makeLowerOrEqual($field, $value, $same_field_op);
                break;
            case '<>':
                return FilterElement::makeNotEqual($field, $value, $same_field_op);
                break;
        }
        return null;
    }
}