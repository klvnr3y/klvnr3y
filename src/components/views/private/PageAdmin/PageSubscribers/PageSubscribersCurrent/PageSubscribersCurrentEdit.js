import React, { useState } from "react";
import {
  Card,
  Col,
  Row,
  Form,
  Collapse,
  Radio,
  Divider,
  message,
  Upload,
  Typography,
  Switch,
  Progress,
  notification,
} from "antd";

import ImgCrop from "antd-img-crop";

import optionCountryCodes from "../../../../../providers/optionCountryCodes";
import optionStateCodesUnitedState from "../../../../../providers/optionStateCodesUnitedState";
import optionStateCodesCanada from "../../../../../providers/optionStateCodesCanada";

import FloatInput from "../../../../../providers/FloatInput";
import FloatSelect from "../../../../../providers/FloatSelect";
import FloatDatePicker from "../../../../../providers/FloatDatePicker";
import FloatInputMask from "../../../../../providers/FloatInputMask";
import moment from "moment";
import { GET, POSTFILE } from "../../../../../providers/useAxiosQuery";
import { apiUrl, userData } from "../../../../../providers/companyInfo";

export default function PageSubscribersCurrentEdit(props) {
  const { location } = props;
  const [form] = Form.useForm();

  const stateUS = optionStateCodesUnitedState();
  const stateCA = optionStateCodesCanada();

  const [selectedData, setSelectedData] = useState();
  const [optionState, setOptionState] = useState([]);
  const [stateLabel, setStateLabel] = useState("State");
  const [optionZip, setOptionZip] = useState();
  const [zipLabel, setZipLabel] = useState("Zip Code");

  const [fileList, setFileList] = useState([]);
  const [radioData, setRadioData] = useState(1);
  const [imageCrop, setImageCrop] = useState({
    width: 1,
    height: 1,
  });

  const [progressData, setProgressData] = useState(0);
  GET(`api/v1/users/${location.state}`, "subscriber_current_edit", (res) => {
    if (res.data) {
      let data = res.data;
      setSelectedData(data);

      let profile_image = data.profile_image.split("//");
      let imgPath = "";
      if (profile_image[0] === "https:") {
        imgPath = data.profile_image;
      } else {
        imgPath = apiUrl + data.profile_image;
      }

      setFileList([
        {
          uid: "-1",
          name: "profile.png",
          status: "done",
          url: imgPath,
        },
      ]);

      let dataProgress = (data.userLessons / data.lessons) * 100;
      setProgressData(dataProgress);

      form.setFieldsValue({
        type: data.role,
        firstname: data.firstname,
        lastname: data.lastname,
        contact_number: data.contact_number,
        google2fa_enable: data.google2fa_enable,
        email: data.email,
        date_signup: moment(data.created_at),
      });

      if (data.company) {
        let company = data.company;

        form.setFieldsValue({
          company_name: company.company_name,
          address1: company.address1,
          address2: company.address2,
          country: company.country,
          state: company.state,
          city: company.city,
          zip: company.zip,
          business_phone: company.business_phone,
        });
      }
    }
  });

  const { mutate: mutateUpdate } = POSTFILE(
    "api/v1/update_profile",
    "update_profile_edit"
  );

  const handleCountry = (e, opt) => {
    if (e === "United States") {
      setOptionState(stateUS);
      setStateLabel("State");
      setOptionZip(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
      setZipLabel("Zip Code");
    } else if (e === "Canada") {
      setOptionState(stateCA);
      setStateLabel("County");
      setOptionZip(/^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/);
      setZipLabel("Postal Code");
    } else {
      setOptionState(stateUS);
      setStateLabel("State");
      setOptionZip(/(^\d{5}$)|(^\d{5}-\d{4}$)/);
      setZipLabel("Zip Code");
    }

    // form2.resetFields(["state"]);
  };

  const handleResize = (val) => {
    console.log("val", val.target.value);
    setRadioData(val.target.value);
    if (val.target.value === 1) {
      setImageCrop({
        width: 1,
        height: 1,
      });
    } else if (val.target.value === 2) {
      setImageCrop({
        width: 3.9,
        height: 2.6,
      });
    } else if (val.target.value === 3) {
      setImageCrop({
        width: 1,
        height: 1.5,
      });
    }
  };

  const onChangeUpload = ({ fileList: newFileList }) => {
    var _file = newFileList;
    if (_file.length !== 0) {
      _file[0].status = "done";
      setFileList(_file);
      form.submit();
    } else {
      setFileList([]);
    }
  };

  const onPreviewUpload = async (file) => {
    let src = file.url;
    if (!src) {
      src = await new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file.originFileObj);
        reader.onload = () => resolve(reader.result);
      });
    }
    const image = new Image();
    image.src = src;
    const imgWindow = window.open(src);
    imgWindow.document.write(image.outerHTML);
  };

  const beforeUpload = (file) => {
    const isJpgOrPng =
      file.type === "image/jpeg" ||
      file.type === "image/png" ||
      file.type === "image/gif" ||
      file.type === "image/jpg";
    if (!isJpgOrPng) {
      message.error("You can only upload JPG, PNG, GIF, JPEG file!");

      return;
    }
    const isLt2M = file.size / 102400 / 102400 < 10;
    if (!isLt2M) {
      message.error("Image must smaller than 10MB!");

      return;
    }

    return Upload.LIST_IGNORE;
  };

  const onFinish = (values) => {
    let data = new FormData();
    data.append("id", location.state);
    data.append("firstname", values.firstname);
    data.append("lastname", values.lastname);
    data.append(
      "contact_number",
      values.contact_number ? values.contact_number : ""
    );
    data.append("role", userData().role);
    data.append("company_name", values.company_name ? values.company_name : "");
    data.append("address1", values.address1 ? values.address1 : "");
    data.append("address2", values.address2 ? values.address2 : "");
    data.append("country", values.country ? values.country : "");
    data.append("state", values.state ? values.state : "");
    data.append("city", values.city ? values.city : "");
    data.append("zip", values.zip ? values.zip : "");
    data.append(
      "business_phone",
      values.business_phone ? values.business_phone : ""
    );

    if (fileList.length > 0) {
      if (fileList[0].originFileObj !== undefined) {
        data.append(
          "profile_image",
          fileList[0].originFileObj,
          fileList[0].name
        );
      }
    }

    mutateUpdate(data, {
      onSuccess: (res) => {
        if (res.success) {
          notification.success({
            message: "User Update",
            description: res.message,
          });
        } else {
          notification.error({
            message: "User Update",
            description: res.message,
          });
        }
      },
      onError: (err) => {
        notification.error({
          message: "User Update",
          description: err.response.data.message,
        });
      },
    });
  };

  const handleInputBlur = (value, field) => {
    if (field === "contact_number") {
      if (value !== undefined) {
        let newval = value.split("_").join("");
        newval = newval.split(" ").join("");
        if (selectedData[field] !== newval) {
          form.submit();
        }
      }
    } else {
      if (selectedData[field] !== value) {
        form.submit();
      }
    }
  };

  return (
    <Card
      className="page-admin-subscriber-edit"
      id="PageSubscribersCurrentEdit"
    >
      <Form form={form} onFinish={onFinish}>
        <Row gutter={20}>
          <Col xs={24} sm={24} md={16}>
            <Collapse
              className="main-1-collapse border-none"
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
              <Collapse.Panel
                header="USER TYPE"
                key="1"
                className="accordion bg-darkgray-form m-b-md border bgcolor-1 white"
              >
                <Row gutter={8}>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="type" className="form-select-error">
                      <FloatSelect
                        label="User Type"
                        placeholder="User Type"
                        options={[
                          {
                            value: "ALL",
                            label: "All",
                          },
                          {
                            value: "Cancer Caregiver",
                            label: "Cancer Caregiver",
                          },
                          {
                            value: "Cancer Care Professional",
                            label: "Cancer Care Professional",
                          },
                        ]}
                      />
                    </Form.Item>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>

            <Collapse
              className="main-1-collapse border-none"
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
              <Collapse.Panel
                header="PERSONAL INFORMATION"
                key="1"
                className="accordion bg-darkgray-form m-b-md border "
              >
                <Row gutter={12}>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="firstname">
                      <FloatInput
                        label="First Name"
                        placeholder="First Name"
                        onBlurInput={(e) => handleInputBlur(e, "firstname")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="lastname">
                      <FloatInput
                        label="Last Name"
                        placeholder="Last Name"
                        onBlurInput={(e) => handleInputBlur(e, "lastname")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="contact_number">
                      <FloatInputMask
                        label="Cell Phone"
                        placeholder="Cell Phone"
                        maskLabel="contact_number"
                        maskType="999 999 9999"
                        onBlurInput={(e) =>
                          handleInputBlur(e, "contact_number")
                        }
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12} className="m-b-md">
                    <div
                      style={{
                        display: "flex",
                        justifyContent: "space-between",
                        height: "100%",
                        alignItems: "center",
                      }}
                    >
                      <div>2-Factor Authentication</div>
                      <div>
                        <Form.Item
                          name="google2fa_enable"
                          valuePropName="checked"
                        >
                          <Switch disabled />
                        </Form.Item>
                      </div>
                    </div>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="email">
                      <FloatInput
                        label="email"
                        placeholder="email"
                        disabled={true}
                      />
                    </Form.Item>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>

            <Collapse
              className="main-1-collapse border-none"
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
              <Collapse.Panel
                header="COMPANY INFORMATION"
                key="1"
                className="accordion bg-darkgray-form m-b-md border"
              >
                <Row gutter={8}>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="company_name">
                      <FloatInput
                        label="Company Name"
                        placeholder="Company Name"
                        onBlurInput={(e) => handleInputBlur(e, "company_name")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="address1">
                      <FloatInput
                        label="Address 1"
                        placeholder="Address 1"
                        onBlurInput={(e) => handleInputBlur(e, "address1")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="address2">
                      <FloatInput
                        label="Address 2"
                        placeholder="Address 2"
                        onBlurInput={(e) => handleInputBlur(e, "address2")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="country" className="form-select-error">
                      <FloatSelect
                        label="Country"
                        placeholder="Country"
                        options={optionCountryCodes}
                        onChange={handleCountry}
                        onBlurInput={(e) => handleInputBlur(e, "country")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="state" className="form-select-error">
                      <FloatSelect
                        label={stateLabel}
                        placeholder={stateLabel}
                        options={optionState}
                        onBlurInput={(e) => handleInputBlur(e, "state")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="city">
                      <FloatInput
                        label="City"
                        placeholder="City"
                        onBlurInput={(e) => handleInputBlur(e, "city")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item
                      name="zip"
                      hasFeedback
                      className="w-100"
                      rules={[
                        {
                          required: optionZip ? true : false,
                          message: "This field is required.",
                        },
                        {
                          pattern: optionZip,
                          message: "Invalid Zip",
                        },
                      ]}
                    >
                      <FloatInput
                        label={zipLabel}
                        placeholder={zipLabel}
                        onBlurInput={(e) => handleInputBlur(e, "zip")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="business_phone">
                      <FloatInputMask
                        label="Business Phone"
                        placeholder="Business Phone"
                        maskLabel="business_phone"
                        maskType="999 999 9999"
                        onBlurInput={(e) =>
                          handleInputBlur(e, "business_phone")
                        }
                      />
                    </Form.Item>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>
          </Col>

          <Col xs={24} sm={24} md={8}>
            <Collapse
              className="main-1-collapse border-none"
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
              <Collapse.Panel
                header="PROFILE PHOTO"
                key="1"
                className="accordion bg-darkgray-form m-b-md border "
              >
                <Row gutter={12}>
                  <Col xs={24} sm={24} md={24} className="text-center">
                    <label className="font-red">
                      <b>Photo upload & cropping: select image orientation</b>
                    </label>
                    <br />
                    <Radio.Group onChange={handleResize} value={radioData}>
                      <Radio value={1}>Square</Radio>
                      <Radio value={2}>Rectangle</Radio>
                      <Radio value={3}>Portrait</Radio>
                    </Radio.Group>
                  </Col>
                  <Divider />
                  <Col xs={24} sm={24} md={24}>
                    <div className="flex">
                      <ImgCrop
                        rotate
                        aspect={imageCrop.width / imageCrop.height}
                      >
                        <Upload
                          // action="https://www.mocky.io/v2/5cc8019d300000980a055e76"
                          accept="image/*"
                          listType="picture-card"
                          style={{ width: "200px" }}
                          maxCount={1}
                          action={false}
                          customRequest={false}
                          fileList={fileList}
                          onChange={onChangeUpload}
                          onPreview={onPreviewUpload}
                          beforeUpload={beforeUpload}
                          className="profilePhoto"
                        >
                          {fileList.length < 1 && "+ Upload"}
                        </Upload>
                      </ImgCrop>
                    </div>
                  </Col>
                  <Divider />
                  <Col xs={24} sm={24} md={24}>
                    <Typography.Text strong>
                      One file only. 10 MB limit.
                      <br />
                      Selected profile photo will be visible to all users.
                    </Typography.Text>
                    <br />

                    <Typography.Text disabled>
                      Allowed types png, gif, jpeg.
                    </Typography.Text>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>

            <Collapse
              className="main-1-collapse border-none"
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
              <Collapse.Panel
                header="SIGN UP DATE & PROGRESS"
                key="1"
                className="accordion bg-darkgray-form m-b-md border bgcolor-1 white"
              >
                <Row gutter={8}>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="date_signup">
                      <FloatDatePicker
                        label="Date"
                        placeholder="Date"
                        disabled={true}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Progress
                      percent={progressData}
                      strokeWidth={20}
                      strokeColor="#0e5729"
                    />
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>
          </Col>
        </Row>
      </Form>
    </Card>
  );
}
