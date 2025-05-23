# Alice based reference data import

Neos Flow package to import reference data into your project based on [Swisscom.AliceConnector](https://github.com/onivaevents/Swisscom.AliceConnector).

This allows to define the reference data in YAML, JSON or PHP files. YAML example:

```yaml
Your\Package\Domain\Model\Vat:
  vat_normal:
    name: 'Normal'
    rate: '7.7'
  vat_special:
    name: 'Special'
    rate: '3.7'
  vat_reduced:
    name: 'Reduced'
    rate: '3.5'
```

See the readme in [Swisscom.AliceConnector](https://github.com/onivaevents/Swisscom.AliceConnector) or [Alice](https://github.com/nelmio/alice) for further documentation.


## Getting started

Install the package through composer.

```
composer require swisscom/referencedataimport
```

### Configuration

Set the path to your reference data files in the ``Settings.yaml``. The path is specified as a fixture set with the `referenceData` key as part of the `Swisscom.AliceConnector` package.

```yaml
Swisscom:
  AliceConnector:
    fixtureSets:
      referenceData: '%FLOW_PATH_PACKAGES%Application/Your.Package/Resources/Private/ReferenceData/{name}.yaml''
```

### Annotations

Annotate the data model classes that should get imported with `Swisscom\ReferenceDataImport\Annotation\Entity`.
If the property should get updated on further executions, annotate it with `Updatable`.

```php
<?php
namespace Your\Package\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Swisscom\ReferenceDataImport\Annotation as ReferenceData;

/**
 * @Flow\Entity
 * @ReferenceData\Entity
 */
class Vat
{
    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var float
     * @ReferenceData\Updatable
     */
    protected $rate;
}
```

### Repository

Each entity repository that supports reference data import should implement the `\Swisscom\ReferenceDataImport\ReferenceDataRepositoryInterface`.
It defines the method findByReferenceDataEntity which is called to determine wheter the object exists already or not.

Example for the `Your\Package\Domain\Repository\VatRepository`:
```php
public function findByReferenceDataEntity(object $object): ?Vat
{
    $query = $this->createQuery();
    return $query->matching($query->equals('name', $object->getName()))->execute()->getFirst();
}
```

## Usage

The data are imported via the CLI:
```shell
./flow referencedata:import
```

## Limitation

Object relations (OneToMany, ManyToMany, ManyToOne) annotated with `Updatable` will point to the newly imported object even if the source object is existing. Handling those relations need to be done individually. The provided signals may be the entry point therefore.
