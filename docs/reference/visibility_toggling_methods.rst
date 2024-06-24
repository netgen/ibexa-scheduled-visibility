Visibility toggling methods
===========================

Scheduled visibility comes with several methods you can use for toggling content visibility using
different services from `Ibexa PHP API <https://doc.ibexa.co/en/latest/api/php_api/php_api/>`_:

.. contents::
    :depth: 1
    :local:

Location
--------

Depending on the version of the Ibexa installed, this method will use either
`hideContent() <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-ContentService.html#method_hideContent>`_ and
`revealContent() <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-ContentService.html#method_revealContent>`_ methods from
`ContentService <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-ContentService.html>`_ or
`hideLocation() <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-LocationService.html#method_hideLocation>`_ and
`unhideLocation() <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-LocationService.html#method_unhideLocation>`_ methods from
`LocationService <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-LocationService.html>`_.

Section
-------

This method will use `SectionService <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-SectionService.html>`_
and its method `assignSection() <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-SectionService.html#method_assignSection>`_.
Content that is supposed to become hidden or visible will be assigned to :ref:`configured<section_configuration>` sections.

Object state
------------

This method will use `ObjectStateService <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-ObjectStateService.html>`_
and its method `setContentState() <https://doc.ibexa.co/en/latest/api/php_api/php_api_reference/classes/Ibexa-Contracts-Core-Repository-ObjectStateService.html#method_setContentState>`_.
Content that is supposed to be hidden or visible will be assigned to :ref:`configured<object_state_configuration>` object states in object state group.

.. note::

    Both object states must be in the configured object state group.
