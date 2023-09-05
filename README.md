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
