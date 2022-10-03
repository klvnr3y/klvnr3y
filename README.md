# SHAMCEY THEME

## This theme is for 5Pints usage/distribution. Any other usage of this is not allowed.

## FOR LARAVEL GUIDE

- composer install --ignore-platform-reqs (not necessary)

### VIA COMPOSER

- composer create-project laravel/laravel example-app
- laravel new example-app
- php artisan serve

### INSTALLER

- composer global require laravel/installer
- laravel new example-app
- php artisan serve

### FOR EXISTING

- composer install
- setup .env
- create database
- php artisan key:generate
- php artisan migrate --seed
- php artisan passport:install
- php artisan serve or use valet (recommended for ios or mac)

#### ANOTHER COMMAND

- composer update
- composer dump-autoload
- php artisan migrate:fresh --seed
- php artisan db:seed
- php artisan make:migration create_form_types_table
- php artisan make:model User -c --api (auto generate for model and controller and api)
- php artisan make:model User -mc --api (auto generate for migration table, model, controller and api)
- php artisan make:observer UserObserver --model=User
- php artisan make:seeder UserSeeder
- php artisan make:migration add_status_to_users_table --table=users (status is the column and the users is the table name)
- php artisan make:migration create_inventory_view (inventory is the name of your view din sumpayi ug view hehe)
- php artisan storage:link

```
public function up()
{
    DB::statement("CREATE OR REPLACE VIEW inventory_views AS
        SELECT
            inventories.*,
            COALESCE(bgry, 0) AS total_barangay,
            COALESCE(mmbr, 0) AS total_member,
            (inventories.quantity - (COALESCE(bgry, 0) + COALESCE(mmbr, 0))) as `remaining`
        FROM inventories
        LEFT JOIN (
            SELECT
                SUM(quantity) AS bgry, inventory_tagging_barangays.*
            FROM inventory_tagging_barangays
            WHERE deleted_by is NULL
            GROUP BY inventory_id
        ) bgrys ON bgrys.inventory_id = inventories.id AND bgrys.created_by = inventories.created_by
        LEFT JOIN (
            SELECT
                SUM(quantity) AS mmbr, inventory_tagging_members.*
            FROM inventory_tagging_members
            WHERE deleted_by is NULL AND barangay_id = '0'
            GROUP BY inventory_id
        ) mmbrs ON mmbrs.inventory_id = inventories.id AND mmbrs.created_by = inventories.created_by");
}

public function down()
{
    DB::statement("DROP VIEW inventory_views");
}
```

- php artisan serve --host=some.other.domain --port=8000

## FOR REACT

- npm install
- npm i --save ignore-errors (not necessary)
- npm install simple-encryptor --save
- npm run watch (para ma kita nimo ang updates sa imong kinabuhi)
- [react-quill] https://github.com/zenoamaro/react-quill

## LOGIN INFO

- default user: admin@test.com
- default pass: admin123

# FOR GIT

### Pag mag merge sa master

- branch branch_name (imong branch name nga imong gipangalan)
- git add .
- git commit -m "comment sa updates sa imong kinabuhi char haha"
- git push or git push origin branch_name (e push nimo ang imong updates sa imong kinabuhi para dili mawala)
- git checkout master (master is the main branch)
- git pull (e pull permi ky basin naay updates sa ilang kinabuhi char haha)
- git merge branch_name (e merge nimo para makabalo pud sila sa update sa imong kinabuhi char hahaha)
- git checkout -b 20220830_klaven_update
- git push

php artisan make:migration add_sex_name_asidjais_asdjajsd_in_table_joshua --table=joshua_table

### start work

- git checkout master (master is the main branch)
- git pull (e pull jud permi ky basin naay updates sa ilang kinabuhi haha)
- git checkout branch_name (name sa imong branch)
- git merge master (para masagol na sa imong kinabuhi)

## FOR OTHER NOTES

add x if done between "[ ]"

[https://www.telerik.com/kendo-react-ui/components/pdfprocessing/get-started/](https://www.telerik.com/kendo-react-ui/components/pdfprocessing/get-started/)
[https://react-pdf.org/](https://react-pdf.org/)
[https://stackblitz.com/edit/react-o4uav8?file=app/main.jsx](https://stackblitz.com/edit/react-o4uav8?file=app/main.jsx)
[https://stackoverflow.com/questions/56752113/export-to-pdf-in-react-table](https://stackoverflow.com/questions/56752113/export-to-pdf-in-react-table)
[https://medium.com/nerd-for-tech/react-to-pdf-printing-f469cc99b24a](https://medium.com/nerd-for-tech/react-to-pdf-printing-f469cc99b24a)

- dangerouslySetInnerHTML={{__html: dataRecord.inventory[0].description}}

- php artisan queue:work

- Form Select
  ` <Form.Item className="form-select-error" name="event_type" rules={[validator.require]} hasFeedback > <FloatSelect label="Select Live In-Person or Virtual Event" placeholder="Select Live In-Person or Virtual Event" options={[ { label: "Live In-Person", value: "Live In-Person", }, { label: "Virtual Event", value: "Virtual Event", }, ]} /> </Form.Item>`

- Form Select Multiple
  ` <Form.Item className="form-select-error-multi" name="event_type" rules={[validator.require]} hasFeedback > <FloatSelect label="Select Live In-Person or Virtual Event" placeholder="Select Live In-Person or Virtual Event" options={[ { label: "Live In-Person", value: "Live In-Person", }, { label: "Virtual Event", value: "Virtual Event", }, ]} multi="multiple" /> </Form.Item>`

- Float Input Mask
  `<Form.Item name="cell_number" rules={[validator.require]} hasFeedback > <FloatInputMask label="Cell Phone" placeholder="Cell Phone" maskLabel="cell_phone" maskType="999-999-9999" /> </Form.Item>`

- icons
  [https://react-icons.github.io/react-icons/] https://react-icons.github.io/react-icons/

- Text Area

```
    <Form.Item
        name="additional_legal_credentials"
        rules={[validator.require]}
        className="input-text-area-label"
    >
        <FloatTextArea
            label="Additional Legal Credentials"
            placeholder="Additional Legal Credentials"
        />
    </Form.Item>
```

- empty blank page

```

import { Card, Col, Row, Collapse } from "antd";
import ComponentHeader from "../Components/ComponentHeader";
import { faUser } from "@fortawesome/pro-solid-svg-icons";

export default function PageFaqs({ match, permission }) {
  const { Panel } = Collapse;
  return (
    <>
      <ComponentHeader title={permission} sub_title="SEARCH" icon={faUser} />

      <Card>
        <Row gutter={12}>
          <Col span={24}>
            <Collapse
              className="ant-collapse-primary"
              expandIcon={({ isActive }) =>
                isActive ? (
                  <span
                    className="ant-menu-submenu-arrow"
                    style={{ color: "#FFF", transform: "rotate(270deg)" }}
                  ></span>
                ) : (
                  <span
                    className="ant-menu-submenu-arrow"
                    style={{ color: "#FFF", transform: "rotate(90deg)" }}
                  ></span>
                )
              }
              expandIconPosition="right"
              defaultActiveKey={["1"]}
            >
              <Panel
                header="Login Information"
                key="1"
                className="accordion bg-darkgray-form"
                extra={
                    <FontAwesomeIcon
                        onClick={() => remove(name)}
                        icon={faTrash}
                    />
                }
              >
                asdjkhds
              </Panel>
            </Collapse>
          </Col>
        </Row>
      </Card>
    </>
  );
}

```

- Left icon collapse

```

<Collapse
    className="main-1-collapse"
    expandIcon={({ isActive }) =>
        isActive ? (
        <span
            className="ant-menu-submenu-arrow"
            style={{ color: "#FFF", transform: "rotate(270deg)" }}
        ></span>
        ) : (
        <span
            className="ant-menu-submenu-arrow"
            style={{ color: "#FFF", transform: "rotate(90deg)" }}
        ></span>
        )
    }
    defaultActiveKey={["1"]}
    expandIconPosition="start"
>
    <Panel
        header="LOGIN INFORMATION"
        key="1"
        className="accordion bg-darkgray-form"
        extra={
            <FontAwesomeIcon
                icon={faTrash}
                onClick={(event) => {
                    alert("asd");
                }}
            />
        }
    >
        <span>
        Yes, we offer testing packages for all ages and sports.
        </span>
    </Panel>
</Collapse>

```
