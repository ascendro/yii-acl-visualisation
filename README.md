yii-acl-visualisations Extension
=====================

![Example Usage](http://i.imgur.com/IZmbXk4.png)

Yii ACL Module Extension for visualizing rights.
Provided is a component which has functions for gaining an array of objects and their permissions between each other
and provides a special function to generate GraphViz Code if needed.

Installation
============

1. Install and configure the required modules:
   acl: https://github.com/ascendro/acl
   graphviz: https://github.com/ascendro/yii-graphviz (optional)
   It is important that the Strategy class of acl is accessable

2. Clone this repository to the extension folder of your yii app(myApp/protected/extensions)

3. Import the component directory Yii::import("ext.yii-acl-visualisations.components.*");

4. Create an instance of the inspector class: $inspector = new ACLInspector();

5. Use the functionality


ChangeLog
---------

See https://github.com/ascendro/yii-acl-visualisation for an incremental list of changes

Contact
-------

http://www.ascendro.de/