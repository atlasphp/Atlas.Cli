# CHANGELOG

## 2.2.2

- Fix #23: fix property return type for ManyToMany

## 2.2.1

Transformation of words ending in double-s now handled more approproately. To
wit, changes the regex for "ending in 's'" to be "ending in a *single* 's'" so
that words like "address", "class", "illness", "pass", "press", etc. are not
unexpectedly truncated.

Also added documentation about custom templates.

## 2.2.0

The skeleton generator command now accepts a second argument: a dot-separated
list of array elements leading to nested Atlas configuration values.

## 2.1.0

The Transform class now takes an array of table-to-type name conversions as
its only constructor param, and no longer converts "status" to "statu". Updated
the relevant documentation.

## 2.0.0

First stable release.

## 2.0.0-beta6

Now adds a Table::DRIVER constant indicating the PDO driver used for the table.

Fix: adds `use Atlas\Table\Row` to Type.tpl, since it is used as an argument
typehint in the docblock.

## 2.0.0-beta5

Fix: adds @property docblocks for relationships only on Fields, not Rows.

## 2.0.0-beta4

Now adds @property docblocks to the Fields trait for defined relationships.

## 2.0.0-beta3

Adds a docblock typehint for Record::getRow().

## 2.0.0-beta2

This release fixes a docblock typehint on generated Table classes.

## 2.0.0-beta1

Major break from alpha1 (and the previous 1.x series) in that the generated
class names are significantly renamed.

In particular, the mapper class no longer has a "Mapper" suffix on it. This
helps it to act as a "base prefix" for IDE completion, which is now greatly
enhanced by the automatic addition of @method docblocks on the generated classes
to indicate type-specific returns.

If you have generated classes with the alpha1 release, you will need to re-
generate them and change your class references.

## 2.0.0-alpha1

First alpha release.

