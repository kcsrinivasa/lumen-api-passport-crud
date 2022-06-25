![Lumen](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/lumen.png?raw=true)


# Lumen Passport API authentication with crud application

Hi All!

**Lumen** is a *micro framework* that means smaller, simpler, leaner, and faster; Lumen is primarily used to build for microservices with loosely coupled components that reduce complexity and enhance the improvements easily.

Here is the example focused on lumen `passport authentication`, `validate request`, `send mail`,`factory`,`upload file` and `rest api crud` application to handle the rest API CRUD authenticated or unauthenticated requests. also install and use of lumen-passport, artisan make command package.

**Representational state transfer (REST)** is a software architectural style that defines a set of constraints to be used for creating Web services.

**Lumen Passport** is an OAuth 2.0 server implementation for API authentication using Lumen. Since tokens are generally used in API authentication, Lumen Passport provides an `easy` and `secure` way to implement token authorization on an `OAuth 2.0 server`.

**Traits** allow us to develop a reusable piece of code and inject it in controller and modal in a Lumen application.

In this example we have focused on Rest API `login`, `register`, `forgot password`, `reset password`, `logout` the user and `create`, `read`, `update`, `delete` the posts using authenticated routes and fetch the posts based on user. and test the API request using postman tool.

For all routes requests must contain in header.
```
'headers' => [
    'Accept' => 'application/json',
]
```

For authenticated route requests must contain in header.
```
'headers' => [
    'Accept' => 'application/json',
    'Authorization' => 'Bearer '. $accessToken,
]
```

### Preview using postman
login
![login](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/login.png?raw=true)
register
![register](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/register.png?raw=true)
Forgot Password mail with OTP
![otp_mail](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/mail_otp.png?raw=true)
validation 
![validation](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/validation_error.png?raw=true)
post list
![post_list](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/post_list.png?raw=true)
create post
![post_create](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/output/post_create.png?raw=true)

Here are the following steps to achive lumen api crud application with passport authentication. 

### Step 1: Install Lumen
```bash
composer create-project lumen/lumen lumen-passport
#and generate key
php artisan key:generate
```

### Step 2: Install Artisan make command package
```bash
composer require flipbox/lumen-generator
#and register in bootstrap/app.php
$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
```

### Step 3: Install passport package
```bash
composer require dusterio/lumen-passport
#and register in bootstrap/app.php
$app->register(lumen\Passport\PassportServiceProvider::class);
```

### Step 4: Uncomment the following in bootstrap/app.php
```bash
$app->withFacades();
$app->withEloquent();
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);
```

### Step 5: Create user migration 
```bash
php artisan make:migration create_users_table --create=users
```
Update the schema in database/migrations/...create_users_table.php file
```bash
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('image')->nullable();
    $table->string('otp')->nullable();
    $table->timestamp('otp_created_at')->nullable();
    $table->timestamps();
});
```
### Step 6: Create AuthController 
```bash
php artisan make:controller AuthController
```
Grab complete Auth Controller from [app/Http/Controllers/AuthController.php](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/app/Http/Controllers/AuthController.php)

### Step 7: Create auth config
Create `config/auth.php` file. If config folder not exist create it. and add the below code
```bash
<?php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
];
```
```bash
#and register in bootstrap/app.php
$app->configure('auth');
```

### Step 8: Configure Passport Module in `app/Models/User.php`
```bash
<?php

namespace App\Models;
....
use lumen\Passport\HasApiTokens;
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable, HasFactory;
...
```
Grab complete User model from [app/Models/User.php file](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/app/Models/User.php)

### Step 9: Create passport auth tables and Install encryption keys and other stuff for Passport
```bash
php artisan migrate
php artisan passport:install
```


### Step 10:Install send mail package
```bash
composer require illuminate/mail

#and register in bootstrap/app.php
$app->register(Illuminate\Mail\MailServiceProvider::class);
$app->configure('mail');
```
### Step 11: Create mail config
Create `config/mail.php` file. If config folder not exist create it. and add the below code

Grab complete mail config from [config/mail.php file](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/config/mail.php)

### Step 12: Create mail class
```bash
php artisan make:mail SendForgotPasswordMail
```
Grab complete mail class from [app/Mail/SendForgotPasswordMail.php file](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/app/Mail/SendForgotPasswordMail.php)

Create `sendforgotpasswordmail.blade.php` in resource/views directory

Grab complete mail body from [resource/views/sendforgotpasswordmail.blade.php file](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/resources/views/sendforgotpasswordmail.blade.php)

Load the following in controller for use mail
```bash
use Illuminate\Support\Facades\Mail;
```

### Step 13: Create trait
Create trait directory and trail file in app directory.(app/Traits/ImageTrait.php) 

```bash
<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ImageTrait {

    /**
     * @param Request $request
     * @param fieldname
     * @param directory
     * @return $this|false|string
     */
    public static function uploadFile(Request $request, $fieldname = 'image', $directory = 'images' ) {

        if( $request->hasFile( $fieldname ) ) {

            $file = $request[$fieldname];
            $fileOriginalName = $file->getClientOriginalName(); 
            $extension = $file->extension(); 
            $size = $file->getSize(); 
            $fileName = Str::slug(substr($fileOriginalName,0,200)).time().'.'.$extension; 
            $filePath = 'uploads/'.$directory.'/'; 
            
            $file->move(app()->basePath('public/'.$filePath), $fileName); 
            $filePath = $filePath.$fileName;

            return $filePath;

        }
        return null;
    }

}
```
```bash
#use in controller
use App\Traits\ImageTrait;
#load upload file in controller
$image = ImageTrait::uploadFile($request, 'image', 'users');
```
### Step 14:Create model,controller,factory,resource,migration for Posts
```bash
	php artisan make:model Post -fmcr
```
`f`: factroy, `m`: migration, `c`:controller, `r`:resource function

Grab complete PostController from [app/Http/Controllers/PostController.php file](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/app/Http/Controllers/PostController.php)

Update the schema in database/migrations/...create_posts_table.php file
```bash
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('otp')->nullable();
    $table->timestamp('otp_created_at')->nullable();
    $table->timestamps();
});
```
Update the factory in database/factory/PostFactory.php file
```bash
public function definition(): array
    {
    	return [
    	    'title' => $this->faker->sentence,
            'body' => $this->faker->paragraph
    	];
    }
```
Load the post factory in database/seeders/DatabaseSeeder.php file
```bash
Post::factory(10)->create();
```

Update Post Module in `app/Models/Post.php`
```bash
....
class Post extends Model
{
    use HasFactory;
    protected $fillable = ['title','body'];
...
```

### Step 15: Add Routes in `routes\web.php` file
```bash
$router->group(['prefix'=>'api'],function() use($router){
    /* un authenticated routes */
    $router->post('/login','AuthController@login');
    $router->post('/register','AuthController@register');
    $router->post('/forgot','AuthController@forgot');
    $router->post('/reset','AuthController@reset');
});

$router->group(['prefix'=>'api','middleware'=>'auth'],function() use($router){
    /* authenticated routes */
    $router->post('/logout','AuthController@logout');
    /* crud post routes*/
    $router->get('/posts','PostController@index');
    $router->get('/posts/{id}','PostController@show');
    $router->post('/posts','PostController@store');
    $router->put('/posts/{id}','PostController@update');
    $router->delete('/posts/{id}','PostController@destroy');
});
```

### Step 16: Add credentials in .env
```bash
DB_DATABASE=lumen_passport_crud
DB_USERNAME=root
DB_PASSWORD=db_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=<Enter your Gmail Id>
MAIL_PASSWORD=<Enter your Gmail Password>
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=<Enter from Gmail Id>
MAIL_FROM_NAME=<From user name>
```

### Step 17: Final run and check
```bash
php artisan migrate
php artisan db:seed
mkdir public/uploads
mkdir public/uploads/users
php artisan serve
```
send request with basepath http://localhost:8000/api/*


## Note : Refer the documentation(root dir file) for API requests
[![document-api](https://img.shields.io/badge/Documentation-APIs_(clieck_here)-blue)](https://github.com/kcsrinivasa/lumen-api-passport-crud/blob/main/api-documentation.docx)

