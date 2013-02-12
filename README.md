fuelphp_fixtures_task
=====================

## Install
Move <code>fixtures.php</code> to <code>APPPATH/tasks/</code>.

## Usage
<pre><code>
Usage:
    php oil r fixtures:&lt;command&gt; [&lt;table1&gt; |&lt;table2&gt; |..] [-env=&lt;environment&gt;] [-d=/tmp] [-n=5] 

Commands:
    dump    Create fixtures form database.
    load    Load fixtures into database.

Runtime options:
    -d      directory of fixture (default:  APPPATH/tests/fixture/)
    -env    environment (default: test)
    # with dump command
    -n number of rows in fixtures (default: 5)

Examples:
    php oil r fixtures:dump -env=development
    php oil r fixtures:dump -n=5 -d=/tmp table_name1 table_name2
    php oil r fixtures:load -d=/tmp
    php oil r fixtures:load table_name1 table_name2
</code></pre>
