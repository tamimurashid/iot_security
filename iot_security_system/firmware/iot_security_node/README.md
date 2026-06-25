# IoT Security Node Firmware

This directory contains the firmware for the ESP32-based IoT security node.

## Quick Start with `arduino-cli`

You can use the `arduino-cli` directly from your Mac's terminal to compile and upload this code.

### 1. Find the connected board's port
Plug in your ESP32 board via USB, then run:
```bash
arduino-cli board list
```
Look for a port that resembles `/dev/cu.usbserial-0001` or `/dev/cu.usbmodem-XXXX`.

### 2. Compile the code
Navigate to this directory (`firmware/iot_security_node`) and compile the sketch. The ESP32 generic board type is called `esp32:esp32:esp32`.
```bash
arduino-cli compile --fqbn esp32:esp32:esp32 .
```

### 3. Upload the code
Once it successfully compiles, upload it by passing the port you found in step 1 using the `-p` flag:
```bash
arduino-cli upload -p /dev/cu.usbserial-0001 --fqbn esp32:esp32:esp32 .
```
*(Replace `/dev/cu.usbserial-0001` with the actual port your board is connected to).*

### 4. Open the Serial Monitor
To see the `Serial.print` outputs (like IP addresses, sensor statuses, or motion alerts), use the monitor command. The ESP32 code is set up for `115200` baud:
```bash
arduino-cli monitor -p /dev/cu.usbserial-0001 --config baudrate=115200
```
*(To exit the monitor, press `Ctrl + C`)*.

---
**Tip:** You can combine compiling and uploading into a single step by just running the `upload` command; it will automatically compile the sketch first if there are any changes!
