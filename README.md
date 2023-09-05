
# PotatoService

# Overview
PotatoService is a comprehensive framework for PHP developers. Below is a brief rundown of its characteristics and features:

* **PHP Version Requirement:** 8.1 or higher
* **Status**: Alpha
* **Version**: 0.1

### Architectural Structure
The framework's architecture is divided into three distinct layers:

1. **Aplication Layer**
2. **Domain Layer**
3. **Infrastructure Layer**

### Configuration
1. Project Settings: Modify the .env file to adjust your project settings. Path: infrastructure/.env -> $_ENV
2. Dependencies: Set up and configure dependencies using composer. Path: infrastructure/composer.json

### Resource Management
To map requests by resource and initialize them, use the runner. When adding a new Resource file, update application/runner/Main.php to include the resource class:

```php
Routes::registerResources([
	DemoQueryResource::class
]);
```

For route mapping in your resource file, use the #[Route] attribute. Example:

```php
#[Route(route: 'Home', code: StatusCode::OK, type: HttpRequest::GET, headers: [ ContentType::CONTENT_JS ])]
public function exampleRouting(DemoResquest $demoResquest){
	$this->testeService->execute();
}
```

To map request input data (e.g., Post, Get), create a class extending MapRequest. This allows for attribute-based input validation:

```php
class DemoResquest extends MapRequest { 
	#[MinLength(5)]
	public ?string $nome;
	
	#[NotBlank]
	public ?string $sobrenome;
}
```

### Services
Establish services to encapsulate your business logic within the domain layer, making it accessible in the application layer:

```php
class TesteService extends Services {
	#[Autowired(class: TesteService::class)]
	public TesteService $testeService;
	
	public function execute(){}
}
```

### Exception Handling
Exceptions are categorized into BusinessException and ServerException. These output a JSON error message with HTTP codes 400 and 500, respectively. Custom exceptions can be defined for more detailed error feedback.

### Data Modeling
The framework uses the Eloquent ORM. To define a model, extend EntityModel and apply the #[Entity] attribute:

```php
#[Entity(tableName: 'teste', properties: ['timestamps' => false])]
class Teste extends EntityModel {
	#[Column(name: "id", primaryKey: true)]
	public $id;
	
	#[NotBlank]
	#[Column(name: "nome")]
	public $nome;
}
```

### Attribute System
Several attributes are provided for various purposes:

`#[Route]` Exclusive to Resources. Maps routes.

`#[Transactional]`  For Resources with routes. Any exception triggers a database rollback (tested with MySQL).

`#[Cache]` For Resources. Caches the output response.

`#[Autowired]` For Resources & Services. Manages dependency injection.

Additionally, custom validation attributes can be created by implementing the iValidation interface. By default, numerous validation attributes are available for both EntityModel and MapQuest.

Attributes:
`#[AssertFalse]`
`#[AssertTrue]`
`#[Decimal]`
`#[Email]`
`#[Future]`
`#[FutureOrPresent]`
`#[Max]`
`#[Min]`
`#[MinLength]`
`#[MaxLength]`
`#[Negative]`
`#[NegativeOrZero]`
`#[NotBlank]`
`#[NotEmpty]`
`#[NotNull]`
`#[Numeric]`
`#[Password]`
`#[Past]`
`#[PastOrPresent]`
`#[Pattern]`
`#[PositiveOrZero]`
`#[Url]`

# Console Usage Guide

### Overview
The PotatoService framework offers an interactive console for executing specific commands. This guide provides an overview of the available commands and instructions on how to add new commands.

### Running Commands
To execute a command, navigate to the directory containing the index.php file and use the following format:
```console
php index.php -command
```

### Available Commands [Under Development]

 - **-h**: Displays help or lists available commands. [Under Development]
 - **-exec**: Use to execute commands and not finish the console. [Under Development]
 - **-cache**: Performs operations related to caching. [Under Development]


Example:
To view the help or list available commands:

```console
php index.php -cache clearingRoute
```

### Adding New Commands

1.  **Define a New Command Class**: Create a new class in the `application\console` namespace. Ensure that this class implements the `iConsole` interface.
    
    Example:
    ```php
    namespace application\console;
    
    use infrastructure\core\interfaces\iConsole;
    
    class MyNewCommand implements iConsole {
        public function execute($args, $callback) {
            // Command logic here
        }
    }
    ``` 
    
2.  **Register the Command**: Add an entry for your command in the `$commands` array in the `Console` class.
    
    ```php
    private $commands = [
        // ... existing commands
        '-myNewCommand' => MyNewCommand::class
    ];
    ```
    
3.  Now, you can run your new command using:
    `php index.php -myNewCommand`
