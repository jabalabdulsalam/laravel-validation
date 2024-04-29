<?php

namespace Tests\Feature;

use App\Rules\RegistrationRule;
use App\Rules\Uppercase;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidator()
    {
        $data = [
            "username" => "admin",
            "password" => "12345"
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertTrue($validator->passes());
        self::assertFalse($validator->fails());
    }

    public function testValidatorInvalid()
    {
        $data = [
            "username" => "",
            "password" => ""
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get("username");
        $message->get("password");

        Log::info($message->toJson(JSON_PRETTY_PRINT));

    }

    public function testValidatorValidationException()
    {
        $data = [
            "username" => "",
            "password" => ""
        ];

        $rules = [
            "username" => "required",
            "password" => "required"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        try {
            $validator->validate();
            self::fail("Validation Exception not Thrown");
        } catch (ValidationException $exception) {
            self::assertNotNull($exception->validator);
            $message = $exception->validator->errors();

            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }

    }

    public function testValidatorMultipleRules()
    {
        App::setLocale("id");
        $data = [
            "username" => "jabal",
            "password" => "jabal"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"]
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get("username");
        $message->get("password");

        Log::info($message->toJson(JSON_PRETTY_PRINT));

    }

    public function testValidatorValidData()
    {
        $data = [
            "username" => "admin@moordencreative.com",
            "password" => "rahasia",
            "admin" => true,
            "other" => "xxx"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => "required|min:6|max:20"
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        try {
            $valid = $validator->validate();
            Log::info(json_encode($valid, JSON_PRETTY_PRINT));
        } catch (ValidationException $exception) {
            self::assertNotNull($exception->validator);
            $message = $exception->validator->errors();

            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }

    public function testValidatorInlineMessage()
    {
        $data = [
            "username" => "jabal",
            "password" => "jabal"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"]
        ];

        $messages = [
            "required" => ":attribute harus diisi",
            "email" => ":attribute harus berupa email",
            "min" => ":attribute minimal :min karakter",
            "max" => ":attribute maksimal :max karakter"
        ];

        $validator = Validator::make($data, $rules, $messages);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get("username");
        $message->get("password");

        Log::info($message->toJson(JSON_PRETTY_PRINT));

    }

    public function testValidatorAdditionalValidation()
    {
        $data = [
            "username" => "jabal@moordencreative.com",
            "password" => "jabal@moordencreative.com"
        ];

        $rules = [
            "username" => "required|email|max:100",
            "password" => ["required", "min:6", "max:20"]
        ];

        $validator = Validator::make($data, $rules);
        $validator->after(function (\Illuminate\Validation\Validator $validator) {
            $data = $validator->getData();
            if ($data['username'] == $data['password']) {
                $validator->errors()->add("password", "Password tidak boleh sama dengan Username");
            }
        });
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get("username");
        $message->get("password");

        Log::info($message->toJson(JSON_PRETTY_PRINT));

    }

    public function testValidatorCustomRule()
    {
        $data = [
            "username" => "jabal@moordencreative.com",
            "password" => "jabal@moordencreative.com"
        ];

        $rules = [
            "username" => ["required", "email", "max:100", new Uppercase()],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()]
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get("username");
        $message->get("password");

        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorCustomFunctionRule()
    {
        $data = [
            "username" => "jabal@moordencreative.com",
            "password" => "jabal@moordencreative.com"
        ];

        $rules = [
            "username" => ["required", "email", "max:100", function (string $attribute, string $value, Closure $fail) {
                if (strtoupper($value) != $value) {
                    $fail("The field $attribute must be UPPERCASE");
                }
            }],
            "password" => ["required", "min:6", "max:20", new RegistrationRule()]
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertFalse($validator->passes());
        self::assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get("username");
        $message->get("password");

        Log::info($message->toJson(JSON_PRETTY_PRINT));

    }

    public function testValidatorRuleClasses()
    {
        $data = [
            "username" => "Jabal",
            "password" => "jabal@moordencreative123.com"
        ];

        $rules = [
            "username" => ["required", new In(["Jabal", "Abdul", "Salam"])],
            "password" => ["required", Password::min(6)->letters()->numbers()->symbols()]
        ];

        $validator = Validator::make($data, $rules);
        self::assertNotNull($validator);

        self::assertTrue($validator->passes());

    }

    public function testNestedArray()
    {
        $data = [
            "name" => [
                "first" => "Jabal",
                "last" => "Salam"
            ],
            "address" => [
                "street" => "Lorong Bina Karya",
                "city" => "Nagan Raya",
                "country" => "Indonesia"
            ]
        ];

        $rules = [
            "name.first" => ["required", "max:100"],
            "name.last" => ["max:100"],
            "address.street" => ["required", "max:200"],
            "address.city" => ["required", "max:100"],
            "address.country" => ["required", "max:100"]
        ];

        $validator = Validator::make($data, $rules);
        self::assertTrue($validator->passes());
    }

    public function testNestedIndexArray()
    {
        $data = [
            "name" => [
                "first" => "Jabal",
                "last" => "Salam"
            ],
            "address" => [
                [
                    "street" => "Lorong Bina Karya",
                    "city" => "Nagan Raya",
                    "country" => "Indonesia"
                ],
                [
                    "street" => "Lorong Bina Sentosa",
                    "city" => "Nagan Raya",
                    "country" => "Indonesia"
                ]
            ]
        ];

        $rules = [
            "name.first" => ["required", "max:100"],
            "name.last" => ["max:100"],
            "address.*.street" => ["required", "max:200"],
            "address.*.city" => ["required", "max:100"],
            "address.*.country" => ["required", "max:100"]
        ];

        $validator = Validator::make($data, $rules);
        self::assertTrue($validator->passes());
    }
}
