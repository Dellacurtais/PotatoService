
## PotatoService: API-Centric Framework

**PotatoService** is a comprehensive PHP framework meticulously crafted for API development. Infused with a plethora of features and streamlined methodologies, it stands out as a quintessential tool for creating scalable, reliable, and efficient APIs. Here's a dive into its core functionalities and design philosophies:

1.  **Resource Management**: At the heart of any RESTful API lies the principle of resource orientation. PotatoService offers a structured approach to map requests to specific resources, ensuring a clear and logical organization of endpoints.
    
2.  **Dynamic Routing**: Through the `#[Route]` annotation, the framework brings forth a flexible routing mechanism. This system is intricately designed to cater to various HTTP methods, establishing a foundation for a versatile API handling.
    
3.  **Seamless Input Validation**: APIs often act as gatekeepers, ensuring that only valid data interacts with the system. PotatoService's integrated validation attributes empower developers to define and rigorously check request inputs. This built-in robustness guarantees data integrity and security.
    
4.  **Exception Handling with Clarity**: In the realm of APIs, precise and informative feedback is paramount. PotatoService distinguishes between `BusinessException` and `ServerException`, delivering structured JSON responses. This nuanced approach ensures that clients receive clear and actionable error messages.
    
5.  **Attribute-Driven Design**: One of PotatoService's standout features is its rich attribute system. Attributes like `#[Cache]`, `#[Transactional]`, and various validations play pivotal roles in enhancing performance, ensuring data consistency, and maintaining data integrity.
    
6.  **Interactive Console**: A testament to its comprehensive nature, PotatoService boasts an interactive console. This tool is instrumental for backend operations, be it migrations, cache operations, or other housekeeping tasks, making API maintenance a breeze.


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

# About PotatoService
1.  **Layered Architecture**: PotatoService is structured into distinct layers (Application, Domain, and Infrastructure), facilitating a separation of concerns, leading to more organized and maintainable code.
    
2.  **Simplified Configuration**: The ability to configure the project and its dependencies via `.env` and `composer.json` files streamlines initial setup and ongoing maintenance.
    
3.  **Resource Management**: The setup allows for easy mapping of requests to specific resources, providing a structured way to manage endpoints and associated logic.
    
4.  **Flexible Routing**: The `#[Route]` annotation offers a declarative way to define routes, making the code more readable and easier to follow.
    
5.  **Integrated Validation**: The ability to craft request classes with embedded validation attributes (like `#[MinLength]` and `#[NotBlank]`) simplifies input checking and aids in maintaining data integrity.
    
6.  **Service System**: The clear separation between business logic (in the domain) and application logic promotes neat organization and better code reuse.
    
7.  **Exception Handling**: The framework has integrated handling for business and server exceptions, assisting in providing valuable feedback in case of issues.
    
8.  **Simplified Data Modeling**: With the use of the Eloquent ORM, efficient data model creation and management are possible.
    
9.  **Rich Attribute System**: Several attributes are available for various purposes, ranging from routing to validation, caching, and dependency injection.
    
10.  **Interactive Console**: The integrated console allows for the execution of specific commands, making tasks like migrations, caching operations, and other backend-related functions more accessible.
    
11.  **Extensibility**: The framework's design appears to be built with extensibility in mind. For instance, adding new commands to the console is straightforward, and the attribute system can be expanded as needed.
    

In summary, PotatoService offer a robust combination of essential features for web development, coupled with a well-thought-out architecture that promotes coding best practices and efficiency.
