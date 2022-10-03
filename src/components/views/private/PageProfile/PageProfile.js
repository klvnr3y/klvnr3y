import React, { useState } from "react";
import { useHistory } from "react-router-dom";
import {
  Button,
  Card,
  Checkbox,
  Col,
  Collapse,
  Divider,
  Form,
  message,
  notification,
  Radio,
  Row,
  Switch,
  Typography,
  Upload,
} from "antd";
import ImgCrop from "antd-img-crop";
import optionCountryCodes from "../../../providers/optionCountryCodes";
import optionStateCodesUnitedState from "../../../providers/optionStateCodesUnitedState";
import optionStateCodesCanada from "../../../providers/optionStateCodesCanada";
import FloatInput from "../../../providers/FloatInput";
import FloatSelect from "../../../providers/FloatSelect";
import {
  apiUrl,
  encrypt,
  role,
  userData,
} from "../../../providers/companyInfo";
import { GET, POSTFILE } from "../../../providers/useAxiosQuery";
import SignatureCanvas from "react-signature-canvas";
import ModalDeactivateAcc from "./Components/ModalDeactivateAcc";
import ModaFormChangePassword from "./Components/ModaFormChangePassword";
import FloatInputMask from "../../../providers/FloatInputMask";
import $ from "jquery";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faTimes } from "@fortawesome/pro-regular-svg-icons";

export default function PageProfile() {
  const history = useHistory();
  const [form] = Form.useForm();

  // console.log("userData", userData())

  const stateUS = optionStateCodesUnitedState();
  const stateCA = optionStateCodesCanada();

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

  const [statusDeactivateAcc, setStatusDeactivateAcc] = useState(true);
  const [toggleModalDeactivateAcc, setToggleModalDeactivateAcc] = useState({
    title: "",
    show: false,
  });
  const [toggleModalFormChangePassword, setToggleModalFormChangePassword] =
    useState(false);
  const [selectedData, setSelectedData] = useState();

  GET(`api/v1/users/${userData().id}`, "update_profile_info", (res) => {
    if (res.success) {
      if (res.data) {
        let data = res.data;
        setSelectedData(data);

        form.setFieldsValue({
          username: data.username,
          firstname: data.firstname,
          lastname: data.lastname,
          email: data.email,
          contact_number: data.contact_number,
          referred_by: data.usersRef,
        });

        let image_type = data.profile_image
          ? data.profile_image.split("/")
          : "";

        // console.log("image_type", image_type);
        if (image_type[0] !== undefined) {
          image_type =
            image_type[0] === "https:"
              ? data.profile_image
              : apiUrl + data.profile_image;

          setFileList([
            {
              uid: "-1",
              name: "image.png",
              status: "done",
              url: image_type,
            },
          ]);
        } else {
          setFileList([]);
          image_type = "";
        }
      }
    }
  });

  const { mutate: mutateUpdateProfile } = POSTFILE(
    "api/v1/update_profile",
    "update_profile_opt"
  );

  const advertisementFilter = {
    advert_for: role(),
  };

  GET(
    `api/v1/advertisement?${new URLSearchParams(advertisementFilter)}`,
    "advertisement_data_info",
    (res) => {
      if (res.success) {
        if (res.data) {
          let data = res.data;
          // console.log("res.data", res.data);
          if (role() !== "Admin") {
            /** banner top */
            let adsTop = [];
            $(".top-banner-adss").removeClass("has-data");
            $(".top-banner-adss .top-banner-adss-inner-image").empty();

            data
              .filter((itemFilter) => itemFilter.position === "Top")
              .map((item, index) => {
                if (item.position === "Top") {
                  adsTop.push(
                    `<img class="top-img-banner-${index}" src="${apiUrl}storage/${item.file_path}" width="800" height="80" />`
                  );
                }
                return "";
              });

            if (adsTop.length > 0) {
              $(".top-banner-adss").addClass("has-data");
              $(".top-banner-adss .top-banner-adss-inner-image").append(adsTop);
              $(".top-banner-adss-inner-image img").css("display", "none");

              $(`.top-banner-adss-inner-image img.top-img-banner-0`).css(
                "display",
                "block"
              );

              let topCurrentSlide = 0;
              setInterval(() => {
                if (topCurrentSlide === adsTop.length - 1) {
                  topCurrentSlide = 0;
                } else {
                  topCurrentSlide++;
                }

                $(`.top-banner-adss-inner-image img`).css("display", "none");
                $(
                  `.top-banner-adss-inner-image img.top-img-banner-${topCurrentSlide}`
                ).css("display", "block");
              }, 2000);
            }
            /** end banner top */

            /** banner right */
            let adsRight = [];
            $(".right-banner-adss").removeClass("has-data");
            $(".right-banner-adss .right-banner-adss-inner-image").empty();

            data
              .filter((itemFilter) => itemFilter.position === "Right")
              .map((item, index) => {
                if (item.position === "Right") {
                  adsRight.push(
                    `<img class="right-img-banner-${index}" src="${apiUrl}storage/${item.file_path}" />`
                  );
                }
                return "";
              });
            if (adsRight.length > 0) {
              $(".right-banner-adss").addClass("has-data");
              $(".right-banner-adss .right-banner-adss-inner-image").append(
                adsRight
              );
              $(".right-banner-adss-inner-image img").css("display", "none");

              $(`.right-banner-adss-inner-image img.right-img-banner-0`).css(
                "display",
                "block"
              );

              let rightCurrentSlide = 0;
              setInterval(() => {
                if (rightCurrentSlide === adsRight.length - 1) {
                  rightCurrentSlide = 0;
                } else {
                  rightCurrentSlide++;
                }

                $(`.right-banner-adss-inner-image img`).css("display", "none");
                $(
                  `.right-banner-adss-inner-image img.right-img-banner-${rightCurrentSlide}`
                ).css("display", "block");
              }, 2000);
            }
            /** end banner right */
          }
        }
      }
    }
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
    // console.log("val", val.target.value);
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
    // console.log(_file);
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
    // console.log("onFinish", values);
    let dataForm = new FormData();
    dataForm.append("id", userData().id);
    dataForm.append("firstname", values.firstname);
    dataForm.append("lastname", values.lastname);
    dataForm.append(
      "contact_number",
      values.contact_number ? values.contact_number : ""
    );
    dataForm.append(
      "email_alternative",
      values.email_alternative ? values.email_alternative : ""
    );
    dataForm.append("role", userData().role);
    if (userData().role !== "Cancer Caregiver") {
      dataForm.append(
        "company_name",
        values.company_name ? values.company_name : ""
      );
      dataForm.append("address1", values.address1 ? values.address1 : "");
      dataForm.append("address2", values.address2 ? values.address2 : "");
      dataForm.append("country", values.country ? values.country : "");
      dataForm.append("state", values.state ? values.state : "");
      dataForm.append("city", values.city ? values.city : "");
      dataForm.append("zip", values.zip ? values.zip : "");
      dataForm.append(
        "business_phone",
        values.business_phone ? values.business_phone : ""
      );
    }
    // console.log("fileList", fileList);
    if (fileList.length > 0) {
      if (fileList[0].originFileObj !== undefined) {
        dataForm.append(
          "profile_image",
          fileList[0].originFileObj,
          fileList[0].name
        );
      }
    }

    mutateUpdateProfile(dataForm, {
      onSuccess: (res) => {
        if (res.success) {
          notification.success({
            message: "Profile Info",
            description: res.message,
          });

          let data = res.data;

          localStorage.userdata = encrypt({
            ...data,
          });

          if (data.profile_image) {
            let image_type = data.profile_image.split("/");

            if (image_type[0] === "https:") {
              $(".ant-menu-submenu-profile").attr("src", data.profile_image);
              $(".ant-menu-item-profile .ant-image-img").attr(
                "src",
                data.profile_image
              );
            } else {
              $(".ant-menu-submenu-profile").attr(
                "src",
                apiUrl + data.profile_image
              );
              $(".ant-menu-item-profile .ant-image-img").attr(
                "src",
                apiUrl + data.profile_image
              );
            }
          }

          $(".ant-typography-profile-details-name-info").html(
            res.data.firstname + " " + res.data.lastname
          );
        } else {
          notification.error({
            message: "Profile Info",
            description: res.message,
          });
        }
      },
      onError: (err) => {
        notification.error({
          message: "Profile Info",
          description: err.response.data.message,
        });
      },
    });
  };

  const onChangeSwitch = (checked) => {
    // console.log(`switch to ${checked}`);
  };

  const handleCheckboxDeactivateAccount = (e) => {
    setStatusDeactivateAcc(e.target.checked === true ? false : true);
  };

  const handleClickDeactivateAcc = () => {
    // console.log("handleClickDeactivateAcc");
    let title = "";
    if (role() === "Cancer CareGiver") {
      title = "Cancer CareGiver $25";
    } else {
      title = "Cancer CareProfessional $75";
    }
    setToggleModalDeactivateAcc({ title, show: true });
  };

  const [signature, setSignature] = useState();
  const [signatureValue, setSignatureValue] = useState();

  const handleClearSignature = () => {
    signature.clear();
    setSignatureValue("");
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
    <Card className="page-profile" id="PageProfile">
      <Form form={form} onFinish={onFinish}>
        <Row gutter={[12, 12]}>
          <Col xs={24} sm={24} md={24} lg={24} xl={16}>
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
                header="LOGIN INFORMATION"
                key="1"
                className="accordion bg-darkgray-form m-b-md border bgcolor-1 white"
              >
                <Row gutter={8}>
                  <Col xs={24} sm={24} md={24}>
                    <Form.Item name="username">
                      <FloatInput
                        label="Username"
                        placeholder="Username"
                        disabled={true}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={24}>
                    <Button
                      type="link"
                      className="color-6 m-l-n"
                      onClick={() => setToggleModalFormChangePassword(true)}
                    >
                      Change Password
                    </Button>
                  </Col>
                  {role() !== "Admin" ? (
                    <Col xs={24} sm={24} md={24}>
                      <Button
                        type="link"
                        onClick={() => history.push("/profile/2fa")}
                        className="m-t-md"
                      >
                        <span className="color-8 m-r-xs m-l-n">Set-up</span>{" "}
                        <span className="color-6">
                          2-Factor Authentication (2FA)
                        </span>
                      </Button>
                    </Col>
                  ) : null}
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
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="firstname">
                      <FloatInput
                        label="First Name"
                        placeholder="First Name"
                        onBlurInput={(e) => handleInputBlur(e, "firstname")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="lastname">
                      <FloatInput
                        label="Last Name"
                        placeholder="Last Name"
                        onBlurInput={(e) => handleInputBlur(e, "lastname")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="email">
                      <FloatInput
                        label="email"
                        placeholder="email"
                        disabled={true}
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
                  {role() === "Admin" ? (
                    <Col xs={24} sm={24} md={24} className="m-b-md">
                      <Button
                        type="link"
                        onClick={() => history.push("/profile/2fa")}
                      >
                        <span className="color-6 m-r-xs m-l-n">CLICK HERE</span>{" "}
                        <span className="color-7">
                          to enable 2-Factor Authetication (2FA)
                        </span>
                      </Button>
                    </Col>
                  ) : null}
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="email_alternative">
                      <FloatInput
                        label="Email Address (Alternative)"
                        placeholder="Email Address (Alternative)"
                        onBlurInput={(e) => handleInputBlur(e, "email")}
                      />
                    </Form.Item>
                  </Col>
                  <Col xs={24} sm={24} md={12}>
                    <Form.Item name="referred_by">
                      <FloatInput
                        label="Referred by"
                        placeholder="Referred by"
                        disabled={true}
                      />
                    </Form.Item>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>

            {role() !== "Cancer Caregiver" ? (
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
                  className="accordion bg-darkgray-form m-b-md border "
                >
                  <Row gutter={8}>
                    <Col xs={24} sm={24} md={24}>
                      <Form.Item name="company_name">
                        <FloatInput
                          label="Company Name"
                          placeholder="Company Name"
                          onBlurInput={(e) =>
                            handleInputBlur(e, "company_name")
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col xs={24} sm={24} md={24}>
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
                      <Form.Item name="address1">
                        <FloatInput
                          label="Address 1"
                          placeholder="Address 1"
                          onBlurInput={(e) => handleInputBlur(e, "address1")}
                        />
                      </Form.Item>
                    </Col>
                    <Col xs={24} sm={24} md={12}>
                      <Form.Item name="address2">
                        <FloatInput
                          label="Address 2"
                          placeholder="Address 2"
                          onBlurInput={(e) => handleInputBlur(e, "address2")}
                        />
                      </Form.Item>
                    </Col>
                    <Col xs={24} sm={24} md={8}>
                      <Form.Item name="city">
                        <FloatInput
                          label="City"
                          placeholder="City"
                          onBlurInput={(e) => handleInputBlur(e, "city")}
                        />
                      </Form.Item>
                    </Col>
                    <Col xs={24} sm={24} md={8}>
                      <Form.Item name="state" className="form-select-error">
                        <FloatSelect
                          label={stateLabel}
                          placeholder={stateLabel}
                          options={optionState}
                          onBlurInput={(e) => handleInputBlur(e, "city")}
                        />
                      </Form.Item>
                    </Col>
                    <Col xs={24} sm={24} md={8}>
                      <Form.Item
                        name="zip"
                        hasFeedback
                        className="w-100"
                        rules={[
                          {
                            required: true,
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
            ) : null}
          </Col>

          <Col xs={24} sm={24} md={24} lg={24} xl={8}>
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
                  <Col xs={24} sm={24} md={24}>
                    <label className="font-red">
                      <b>Please select photo orientation</b>
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
                          listType="picture-card"
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
                    <Typography.Text>
                      One file only. 10 MB limit.
                      <br />
                      You selected profile photo will be visible to all users.
                    </Typography.Text>
                    <br />

                    <Typography.Text className="color-secondary">
                      Allowed types png, gif, jpeg.
                    </Typography.Text>
                  </Col>
                </Row>
              </Collapse.Panel>
            </Collapse>

            {role() !== "Admin" ? (
              <>
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
                    header="SUBSCRIPTION"
                    key="1"
                    className="accordion bg-darkgray-form m-b-md border"
                  >
                    <Row gutter={[12, 20]}>
                      <Col xs={24} sm={24} md={24}>
                        <Typography.Title level={3} className="color-1">
                          Cancer Caregiver - $25.00
                        </Typography.Title>
                        <Typography.Text>
                          You are set up for manual payments, you are not on a
                          recurring payment plan.
                        </Typography.Text>
                      </Col>
                      <Col xs={24} sm={24} md={24}>
                        <Button
                          className="btn-main-invert b-r-none w-100"
                          size="large"
                          onClick={() =>
                            history.push("/profile/account/subscription")
                          }
                        >
                          VIEW SUBSCRIPTION
                        </Button>
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
                    header="DEACTIVATE ACCOUNT"
                    key="1"
                    className="accordion bg-darkgray-form m-b-md border"
                  >
                    <Row gutter={[12, 20]}>
                      <Col xs={24} sm={24} md={24}>
                        <Typography.Text>
                          No longer need your account and want to deactivate it?
                        </Typography.Text>
                      </Col>
                      <Col xs={24} sm={24} md={24}>
                        <div className="flex gap10">
                          <div>
                            <Checkbox
                              onChange={handleCheckboxDeactivateAccount}
                            />
                          </div>
                          <div>
                            <Typography.Text>
                              Yes I understand that by deactivating my account I
                              will no longer have access to my account
                              information and all historical data.
                            </Typography.Text>
                          </div>
                        </div>
                      </Col>
                      <Col xs={24} sm={24} md={24}>
                        <Button
                          // className="btn-main-invert-outline-active b-r-none w-100"
                          className="btn-main-invert-outline-active b-r-none w-100"
                          size="large"
                          disabled={statusDeactivateAcc}
                          onClick={handleClickDeactivateAcc}
                        >
                          DEACTIVATE MY ACCOUNT
                        </Button>
                      </Col>
                    </Row>
                  </Collapse.Panel>
                </Collapse>
              </>
            ) : (
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
                  header="CREATE SIGNATURE"
                  key="1"
                  className="accordion bg-darkgray-form m-b-md border "
                >
                  <Row>
                    <Col xs={24} sm={24} md={24}>
                      <Typography.Text strong className="color-15">
                        To create a free digital signature, follow steps below.
                      </Typography.Text>
                      <br />
                      <Typography.Text strong className="m-r-xs color-15">
                        Step 1:
                      </Typography.Text>
                      <Typography.Text className="color-7">
                        Draw Signature
                      </Typography.Text>
                      <br />
                      <Typography.Text strong className="m-r-xs color-15">
                        Step 2:
                      </Typography.Text>
                      <Typography.Text className="color-7">
                        Save png
                      </Typography.Text>
                    </Col>
                    <Col xs={24} sm={24} md={24}>
                      <div className="m-t-md m-b-md">
                        <SignatureCanvas
                          penColor="#000000"
                          canvasProps={{
                            width: 500,
                            height: 200,
                            className: "e_signature_canvas",
                          }}
                          ref={(ref) => setSignature(ref)}
                        />
                      </div>
                    </Col>
                    <Col xs={24} sm={24} md={24}>
                      <div
                        style={{
                          display: "flex",
                          justifyContent: "space-between",
                        }}
                        className="m-b-md"
                      >
                        <Typography.Text strong className="color-9">
                          Make this the default signature
                        </Typography.Text>
                        <div>
                          <Switch
                            className="bgcolor-13"
                            defaultChecked
                            onChange={onChangeSwitch}
                          />
                        </div>
                      </div>
                    </Col>
                    <Col xs={24} sm={24} md={24}>
                      <Button
                        className="btn-main-invert-outline-active b-r-none m-r-sm"
                        size="large"
                        onClick={handleClearSignature}
                      >
                        CLEAR
                      </Button>
                      <Button className="btn-main-invert b-r-none" size="large">
                        SAVE
                      </Button>
                    </Col>
                  </Row>
                </Collapse.Panel>
              </Collapse>
            )}

            <div className="right-banner-adss">
              <div className="right-banner-adss-inner">
                <div
                  className="icon-close"
                  onClick={() => $(".right-banner-adss").hide()}
                >
                  <FontAwesomeIcon icon={faTimes} />
                </div>
                <div className="right-banner-adss-inner-image" />
              </div>
            </div>
          </Col>
        </Row>
      </Form>

      <ModalDeactivateAcc
        toggleModalDeactivateAcc={toggleModalDeactivateAcc}
        setToggleModalDeactivateAcc={setToggleModalDeactivateAcc}
      />

      <ModaFormChangePassword
        toggleModalFormChangePassword={toggleModalFormChangePassword}
        setToggleModalFormChangePassword={setToggleModalFormChangePassword}
      />
    </Card>
  );
}
