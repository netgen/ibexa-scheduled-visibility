Usage
=====

.. contents::
    :depth: 1
    :local:

Content types
-------------

For content to be accepted by scheduled visibility mechanism,
its content type must contain two fields that are either ``ezdate`` or ``ezdatetime``.
Identifiers of these fields must be ``publish_from`` and ``published_to``.

.. warning::
 ``publish_from`` and ``published_to`` fields must not be translatable.

``publish_from``
~~~~~~~~~~~~~~~~~~~~
It represents time from which content becomes visible.
When set to null, content does not have that limit.

``publish_to``
~~~~~~~~~~~~~~~~~~~~
It represents time until content is visible.
When set to null, content does not have that limit.

When both fields are set to null, content is always visible.
Naturally, if value of ``publish_from`` field is greater that value of ``publish_to``,
content will not be accepted by the mechanism.

Command
-------

Bundle contains ``UpdateContentVisibilityCommand`` that searches through configured content and, if necessary, updates its visibility.
It can be executed by: ``bin/console ngscheduledvisibility:content:update-visibility``.
For this mechanism to work how it is intended, this command should be set as cron job.
