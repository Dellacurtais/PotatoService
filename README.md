
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

### Wiki
* [Exception Handling](https://github.com/Dellacurtais/PotatoService/wiki/Exception-Handling)
* [Resource](https://github.com/Dellacurtais/PotatoService/wiki/Resource-Management)
* [Services](https://github.com/Dellacurtais/PotatoService/wiki/Services)
* [Models](https://github.com/Dellacurtais/PotatoService/wiki/Models)
* [Using `doFilter` within Services](https://github.com/Dellacurtais/PotatoService/wiki/Using-%60doFilter%60-within-Services)
* [Attributes](https://github.com/Dellacurtais/PotatoService/wiki/Attributes)
* [Console Usage Guide](https://github.com/Dellacurtais/PotatoService/wiki/Console-Usage-Guide)


## Upcoming Features in PotatoService

PotatoService is continually evolving, with several exciting features on the horizon. Here's a sneak peek into what's coming next:

### 1. Console-Based Migration Management:

-   **Description**: Migration is a pivotal aspect of database management, especially in evolving applications. The upcoming version will introduce a way to handle database migrations directly through the console. This enhancement will make it easier to create, modify, and manage database structures, ensuring that developers can adapt to changing requirements efficiently.
    
-   **Benefits**:
    -   Streamlined database updates without manual SQL scripting.
    -   Version control for database structures.
    -   Efficient rollback capabilities.

### 2. Installation and Configuration via Console:

-   **Description**: Setting up PotatoService will become even more straightforward. A console-based installation and configuration process is in the works. With a series of simple commands, developers will be able to initialize, configure, and fine-tune their PotatoService setup, all from the command line.
    
-   **Benefits**:
    -   Simplified setup process, reducing manual steps.
    -   Customized configuration based on project needs.
    -   Efficient troubleshooting with console feedback.

----------

Stay tuned for these features and more as PotatoService continues its journey to become an even more developer-friendly and robust framework!
