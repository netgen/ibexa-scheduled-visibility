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

Bundle contains ``ToggleContentVisibilityCommand`` that searches through configured content and, if necessary, toggles its visibility.
It can be executed by: ``bin/console ngscheduledvisibility:content:toggle-visibility``.
For this mechanism to work how it is intended, this command should be set as cron job.
