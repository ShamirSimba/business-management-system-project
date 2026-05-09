<?php
// Validator helper for BMS API

class Validator {
    public static function required($fields, $data) {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "$field is required";
            }
        }
        return $errors;
    }

    public static function email($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['Invalid email format'];
        }
        return [];
    }

    public static function min_length($value, $min) {
        if (strlen($value) < $min) {
            return ["Must be at least $min characters long"];
        }
        return [];
    }

    public static function numeric($value) {
        if (!is_numeric($value)) {
            return ['Must be a numeric value'];
        }
        return [];
    }
}