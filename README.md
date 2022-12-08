# PotatoService
New version of PotatoFramework


# About it
PotatoService try implement the CQRS pattern, but it is still in first steps.

* **PHP Version Requirement:** 8.1 or higher
* **Status**: Alpha Release
* **Version**: 0.1

### Potato Service Structure
1. **Aplication Layer**
2. **Domain Layer**
3. **Infrastructure Layer**

### About Settings
1. All your project settings can be done by changing the .env file [infrastructure/.env -> $_ENV]
2. All dependencies can be configured by composer [infrastructure/composer.json]

### Resources
All requests can be mapped by resource and and initialized by the runner, if you created a new Resource file, just go on application/runner/Main.php and put the class in loadResource();

```php
Routes::registerResources([
	DemoQueryResource::class
]);
```

On your resource file, you just use Attribute `#[Route]` to mapping your routes [see: application/resource/DemoQueryResource].

```php
#[Route(route: 'Home', code: StatusCode::OK, type: HttpRequest::GET, headers: [ ContentType::CONTENT_JS ])]
public function exampleRouting(DemoResquest $demoResquest){
	$this->testeService->execute();
}
```

You can map the request input data (Post, Get, etc), just create a class and extend it to MapRequest, after, just set this in yout method. With this you can use attributes to validate all you need.

```php
class DemoResquest extends MapRequest { 
	#[MinLength(5)]
	public ?string $nome;
	
	#[NotBlank]
	public ?string $sobrenome;
}
```

### Services
Create a services to mantain your business rules in domain layer and use it in your application layer

```php
class TesteService extends Services {
	#[Autowired(class: TesteRepository::class)]
	public TesteRepository $testeRepository;
	
	public function execute(){}
}
```

### Exception
We have a BusinessException and ServerException, when any exception is called the system output a json with this error, BussinessException use code 400 and ServerException use 500.

You can create your exceptions and use it to show a more detailed info about the error.

### Repositories
We use a Eloquent ORM and try simulate a small Repository with it. When you create a Model, you just extend it with EntityModel and use attribute in class `#[Entity(tableName: 'teste', properties: ['timestamps' => false])]`

In model you can map your columns table and validate it, ex:

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

After, you create a new Repository class and extend it on `Repository`:

```php
#[SetRepository(entity: Teste::class)]
class TesteRepository extends Repository {}
```

You can use atribte Autowired to load it your application layer or in your services, example:

```php
#[Autowired(class: TesteRepository::class)]
public TesteRepository $testeRepository;
```

### Attributes
We have same attributes

`#[Route]` Can only be used on Resources. Used  to mapping your routes

`#[Transactional]`  Can only be used on Resources with routes and any exeception make a rollback in your database [Tested with MySQL]

`#[Cache]` Can only be used on Resources to create a cache for your output response

`#[Autowired]` Can used by Resources and Services and is related to dependency injection

You can create a validation attribute, just create a class and implement a `iValidation` interface

By default we have same validations attributes and all can be used in `EntityModel` and `MapQuest`

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
